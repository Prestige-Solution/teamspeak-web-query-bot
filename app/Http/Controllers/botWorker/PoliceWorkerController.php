<?php

namespace App\Http\Controllers\botWorker;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sys\TsLogController;
use App\Http\Controllers\tsConfig\BadNameController;
use App\Http\Controllers\tsConfig\TsUriStringHelperController;
use App\Models\tsBot\tsBotLog;
use App\Models\tsBot\tsServerConfig;
use App\Models\tsBotWorkers\tsBotWorkerPolice;
use App\Models\tsBotWorkers\tsBotWorkerPoliceVpnProtection;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\Adapter;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Node;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

class PoliceWorkerController extends Controller
{
    protected int $serverId;

    protected string $qaName;

    protected bool $isBotAlive = false;

    protected Server|Adapter|Host|Node $tsVirtualServer;

    protected TsLogController $logController;

    public function __construct(int $serverId)
    {
        $this->serverId = $serverId;
        $this->logController = new TsLogController('Police-Worker', $this->serverId);
    }

    /**
     * @throws Exception
     */
    public function startPolice(): void
    {
        //get Server config
        $tsServerConfig = tsServerConfig::query()
            ->where('id', '=', $this->serverId)->first();

        if ($tsServerConfig->qa_nickname != null) {
            $this->qaName = $tsServerConfig->qa_nickname;
        } else {
            $this->qaName = $tsServerConfig->qa_name;
        }

        //get uri with StringHelper
        $tsStringHelper = new TsUriStringHelperController();
        $uri = $tsStringHelper->getStandardUriString(
            $tsServerConfig->qa_name,
            $tsServerConfig->qa_pw,
            $tsServerConfig->server_ip,
            $tsServerConfig->server_query_port,
            $tsServerConfig->server_port,
            $this->qaName.'-Police-Worker',
            $this->serverId,
        );

        try {
            $this->tsVirtualServer = TeamSpeak3::factory($uri);
        } catch(Exception $e) {
            $this->logController->setCustomLog(
                $this->serverId,
                tsBotLog::FAILED,
                'Start Police-Worker',
                'There was an error while attempting to communicate with the server',
                $e->getCode(),
                $e->getMessage()
            );

            return;
        }

        //policeWorker Settings
        $policeWorkerSetting = tsBotWorkerPolice::query()->where('server_id', '=', $this->serverId)->first();

        //check VPN
        if ($policeWorkerSetting->is_vpn_protection_active == true && ! empty($policeWorkerSetting->vpn_protection_api_register_mail)) {
            $this->checkVpn($policeWorkerSetting);
        }

        //check bot is working
        if ($policeWorkerSetting->is_check_bot_alive_active == true) {
            $this->checkBotKeepAlive();
        }

        //check bad names
        if ($policeWorkerSetting->is_bad_name_protection_active == true) {
            $this->checkBadName();
        }

        $this->tsVirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
    }

    private function checkVpn($policeWorkerSetting): void
    {
        try {
            //api police / max 15 per Minute and 500 per day
            //query Count over all Server
            $apiQueryCountSum = tsBotWorkerPolice::query()
                ->where('server_id', '=', $this->serverId)
                ->sum('vpn_protection_query_count');
            //sum query count per day
            $apiQueryCountPerDaySum = tsBotWorkerPolice::query()
                ->where('server_id', '=', $this->serverId)
                ->sum('vpn_protection_query_per_day');

            //max count per Server
            $apiQueryMaxCount = $policeWorkerSetting->vpn_protection_query_max;
            //query count per day
            $apiQueryCountPerDay = $policeWorkerSetting->vpn_protection_query_per_day;
            //api query count per server
            $apiQueryCountServer = $policeWorkerSetting->vpn_protection_query_count;
            //api query per server
            $apiQueryCountThisProcess = 0;

            //if not reach api max query per day
            if ($apiQueryCountPerDaySum <= $policeWorkerSetting->vpn_protection_max_query_per_day) {
                //get clients
                $this->tsVirtualServer->clientListReset();
                $clientList = collect($this->tsVirtualServer->clientList(['clid']));

                foreach ($clientList->keys()->all() as $clid) {
                    $clidInfo = $this->tsVirtualServer->clientGetById($clid)->getInfo();
                    $clidIP = $clidInfo['connection_client_ip'];
                    $checked = false;
                    $kickResult = false;

                    //check if ip is known
                    $knownIpCheck = tsBotWorkerPoliceVpnProtection::query()->where('ip_address', '=', $clidIP)->first();
                    if ($knownIpCheck != null && $knownIpCheck->check_result != 'VPN Detection') {
                        $checked = true;
                    } elseif ($knownIpCheck != null && $knownIpCheck->check_result == 'VPN Detection') {
                        $kickResult = true;
                        $checked = true;
                    }

                    //ignore own sgid
                    if (Str::contains($clidInfo['client_nickname'], $this->qaName) == true || $clidInfo['client_nickname'] == 'serveradmin') {
                        $kickResult = false;
                        $checked = true;
                    }

                    //ignore allowed sgid
                    $sgids = collect(explode(',', $clidInfo['client_servergroups']));
                    foreach ($sgids as $sgid) {
                        if ($policeWorkerSetting->allow_sgid_vpn == $sgid) {
                            $kickResult = false;
                            $checked = true;
                        }
                    }

                    //api checks available
                    if ($apiQueryCountSum <= $apiQueryMaxCount && ! empty($policeWorkerSetting->vpn_protection_api_register_mail) && $checked == false && Carbon::now() >= $policeWorkerSetting->vpn_protection_next_check_available_at) {
                        //api www.getipintel.net/free-proxy-vpn-tor-detection-api
                        $checkIP = Http::get('http://check.getipintel.net/check.php?ip='.$clidIP.'&contact='.$policeWorkerSetting->vpn_protection_api_register_mail.'&flags=m&format=json');

                        if ($checkIP->status() == 200) {
                            $checkIPDecode = $checkIP->json();
                            if ($checkIPDecode['result'] != 0) {
                                //kick is true
                                $kickResult = true;
                                //store ip with check_result = vpn
                                tsBotWorkerPoliceVpnProtection::query()->updateOrCreate(
                                    [
                                        'server_id'=>$this->serverId,
                                        'ip_address'=>$clidIP,
                                    ],
                                    [
                                        'check_result'=>'VPN Detection',
                                    ]
                                );
                            } else {
                                tsBotWorkerPoliceVpnProtection::query()->updateOrCreate(
                                    [
                                        'server_id'=>$this->serverId,
                                        'ip_address'=>$clidIP,
                                    ],
                                    [
                                        'check_result'=>'No VPN',
                                    ]
                                );
                            }

                            //raise query count
                            $apiQueryCountSum = $apiQueryCountSum + 1;
                            $apiQueryCountThisProcess = $apiQueryCountThisProcess + 1;
                            $apiQueryCountServer = $apiQueryCountServer + 1;

                            //proof lock time
                            if ($apiQueryCountSum >= $apiQueryMaxCount) {
                                tsBotWorkerPolice::query()->where('server_id', '=', $this->serverId)->update([
                                    'vpn_protection_next_check_available_at'=>Carbon::now()->addMinutes(15),
                                ]);
                            }
                        }
                    }
                    if ($kickResult == true) {
                        $this->tsVirtualServer->clientPoke($clid, 'VPN was detected. Please report to a Teamspeak administrator or turn off VPN');
                        $this->tsVirtualServer->clientKick($clid, TeamSpeak3::KICK_SERVER, 'VPN was detected. Please report to a Teamspeak administrator or turn off VPN');
                    }
                }

                //set query request count
                tsBotWorkerPolice::query()->where('server_id', '=', $this->serverId)->update([
                    'vpn_protection_query_count'=>$apiQueryCountServer,
                    'vpn_protection_query_per_day'=>$apiQueryCountPerDay + $apiQueryCountThisProcess,
                ]);
            }
        } catch (Exception $e) {
            $this->logController->setCustomLog(
                $this->serverId,
                tsBotLog::FAILED,
                'Check VPN',
                'There was an error during check vpn',
                $e->getCode(),
                $e->getMessage()
            );

            $this->tsVirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
        }
    }

    private function checkBotKeepAlive(): void
    {
        try {
            $checkBotIsWorking = collect($this->tsVirtualServer->clientList(['client_nickname'=>$this->qaName]));

            foreach ($checkBotIsWorking->keys()->all() as $clid) {
                $botQueryName = $this->tsVirtualServer->clientGetById($clid);

                if ($botQueryName['client_nickname'] == $this->qaName) {
                    $this->isBotAlive = true;
                }
            }

            if ($this->isBotAlive === false) {
                $this->logController->setCustomLog(
                    $this->serverId,
                    tsBotLog::SHUTDOWN,
                    'checkBotWork',
                    'No active bot found on the server',
                );

                tsServerConfig::query()
                    ->where('id', '=', $this->serverId)
                    ->update([
                        'bot_status_id'=>tsBotLog::SHUTDOWN,
                    ]);

                $policeWorkerSetting = tsBotWorkerPolice::query()
                    ->where('server_id', '=', $this->serverId)
                    ->first(['discord_webhook_url', 'is_discord_webhook_active']);

                if ($policeWorkerSetting->is_discord_webhook_active == true) {
                    $response = Http::post(Crypt::decryptString($policeWorkerSetting->discord_webhook_url), [
                        'content' => $this->qaName.' is missing on Teamspeak Server. He has probably stopped working',
                        'username' => $this->qaName.'-Police-Worker',
                    ]);

                    if ($response->status() != 204) {
                        $this->logController->setCustomLog(
                            $this->serverId,
                            tsBotLog::FAILED,
                            'checkBotAlive',
                            'Webhook could not be send.',
                        );
                    }
                }
            }
        } catch (Exception $e) {
            $this->logController->setCustomLog(
                $this->serverId,
                tsBotLog::FAILED,
                'Check Bot Keep Alive',
                'There was an error during check bot keep alive',
                $e->getCode(),
                $e->getMessage()
            );

            $this->tsVirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
        }
    }

    private function checkBadName(): void
    {
        $badNameController = new BadNameController();

        try {
            //get all clients
            $this->tsVirtualServer->clientListReset();
            $clientList = collect($this->tsVirtualServer->clientList(['clid']));

            foreach ($clientList->keys()->all() as $clid) {
                //proof only client_type = 0 / 1 = serverquery
                $clidInfo = $this->tsVirtualServer->clientGetById($clid);

                if ($clidInfo['client_type'] == 0) {
                    $badNameProofResult = $badNameController->checkBadName($clidInfo['client_nickname'], $this->serverId);

                    if ($badNameProofResult == true) {
                        //kick client
                        $this->tsVirtualServer->clientPoke($clid, 'Your nickname is not allowed on this server!');
                        $this->tsVirtualServer->clientKick($clid, TeamSpeak3::KICK_SERVER, 'Your nickname is not allowed on this server!');
                    }
                }
            }
        } catch (Exception $e) {
            $this->logController->setCustomLog(
                $this->serverId,
                tsBotLog::FAILED,
                'Check Bad Name',
                'There was an error during check bad name',
                $e->getCode(),
                $e->getMessage()
            );

            $this->tsVirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
        }
    }
}
