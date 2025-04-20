<?php

namespace App\Http\Controllers\botWorker;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sys\Ts3LogController;
use App\Http\Controllers\ts3Config\BadNameController;
use App\Http\Controllers\ts3Config\Ts3UriStringHelperController;
use App\Models\ts3Bot\ts3BotLog;
use App\Models\ts3Bot\ts3ServerConfig;
use App\Models\ts3BotWorkers\ts3BotWorkerPolice;
use App\Models\ts3BotWorkers\ts3BotWorkerPoliceVpnProtection;
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
    protected int $server_id;

    protected string $qa_name;

    protected bool $is_bot_alive = false;

    protected Server|Adapter|Host|Node $ts3_VirtualServer;

    protected Ts3LogController $logController;

    public function __construct(int $server_id)
    {
        $this->server_id = $server_id;
        $this->logController = new Ts3LogController('Police-Worker', $this->server_id);
    }

    /**
     * @throws Exception
     */
    public function startPolice(): void
    {
        //get Server config
        $ts3ServerConfig = ts3ServerConfig::query()
            ->where('id', '=', $this->server_id)->first();

        if ($ts3ServerConfig->qa_nickname != null) {
            $this->qa_name = $ts3ServerConfig->qa_nickname;
        } else {
            $this->qa_name = $ts3ServerConfig->qa_name;
        }

        //get uri with StringHelper
        $ts3StringHelper = new Ts3UriStringHelperController();
        $uri = $ts3StringHelper->getStandardUriString(
            $ts3ServerConfig->qa_name,
            $ts3ServerConfig->qa_pw,
            $ts3ServerConfig->server_ip,
            $ts3ServerConfig->server_query_port,
            $ts3ServerConfig->server_port,
            $this->qa_name.'-Police-Worker',
            $this->server_id,
            $ts3ServerConfig->mode
        );

        try {
            $this->ts3_VirtualServer = TeamSpeak3::factory($uri);
        } catch(Exception $e) {
            $this->logController->setCustomLog(
                $this->server_id,
                ts3BotLog::FAILED,
                'Start Police-Worker',
                'There was an error while attempting to communicate with the server',
                $e->getCode(),
                $e->getMessage()
            );
        }

        //policeWorker Settings
        $policeWorkerSetting = ts3BotWorkerPolice::query()->where('server_id', '=', $this->server_id)->first();

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

        $this->ts3_VirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
    }

    private function checkVpn(array $policeWorkerSetting): void
    {
        try {
            //api police / max 15 per Minute and 500 per day
            //query Count over all Server
            $apiQueryCountSum = ts3BotWorkerPolice::query()
                ->where('server_id', '=', $this->server_id)
                ->sum('vpn_protection_query_count');
            //sum query count per day
            $apiQueryCountPerDaySum = ts3BotWorkerPolice::query()
                ->where('server_id', '=', $this->server_id)
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
                $this->ts3_VirtualServer->clientListReset();
                $clientList = collect($this->ts3_VirtualServer->clientList(['clid']));

                foreach ($clientList->keys()->all() as $clid) {
                    $clidInfo = $this->ts3_VirtualServer->clientGetById($clid)->getInfo();
                    $clidIP = $clidInfo['connection_client_ip'];
                    $checked = false;
                    $kickResult = false;

                    //check if ip is known
                    $knownIpCheck = ts3BotWorkerPoliceVpnProtection::query()->where('ip_address', '=', $clidIP)->first();
                    if ($knownIpCheck != null && $knownIpCheck->check_result != 'VPN Detection') {
                        $checked = true;
                    } elseif ($knownIpCheck != null && $knownIpCheck->check_result == 'VPN Detection') {
                        $kickResult = true;
                        $checked = true;
                    }

                    //ignore own sgid
                    if (Str::contains($clidInfo['client_nickname'], $this->qa_name) == true || $clidInfo['client_nickname'] == 'serveradmin') {
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
                                ts3BotWorkerPoliceVpnProtection::query()->updateOrCreate(
                                    [
                                        'server_id'=>$this->server_id,
                                        'ip_address'=>$clidIP,
                                    ],
                                    [
                                        'check_result'=>'VPN Detection',
                                    ]
                                );
                            } else {
                                ts3BotWorkerPoliceVpnProtection::query()->updateOrCreate(
                                    [
                                        'server_id'=>$this->server_id,
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
                                ts3BotWorkerPolice::query()->where('server_id', '=', $this->server_id)->update([
                                    'vpn_protection_next_check_available_at'=>Carbon::now()->addMinutes(15),
                                ]);
                            }
                        }
                    }
                    if ($kickResult == true) {
                        $this->ts3_VirtualServer->clientPoke($clid, 'VPN was detected. Pleas report to a Teamspeak administrator or turn off VPN');
                        $this->ts3_VirtualServer->clientKick($clid, TeamSpeak3::KICK_SERVER, 'VPN was detected. Pleas report to a Teamspeak administrator or turn off VPN');
                    }
                }

                //set query request count
                ts3BotWorkerPolice::query()->where('server_id', '=', $this->server_id)->update([
                    'vpn_protection_query_count'=>$apiQueryCountServer,
                    'vpn_protection_query_per_day'=>$apiQueryCountPerDay + $apiQueryCountThisProcess,
                ]);
            }
        } catch (Exception $e) {
            $this->logController->setCustomLog(
                $this->server_id,
                ts3BotLog::FAILED,
                'Check VPN',
                'There was an error during check vpn',
                $e->getCode(),
                $e->getMessage()
            );

            $this->ts3_VirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
        }
    }

    private function checkBotKeepAlive(): void
    {
        try {
            $checkBotIsWorking = collect($this->ts3_VirtualServer->clientList(['client_nickname'=>$this->qa_name]));

            foreach ($checkBotIsWorking->keys()->all() as $clid) {
                $BotQueryName = $this->ts3_VirtualServer->clientGetById($clid);

                if ($BotQueryName['client_nickname'] == $this->qa_name) {
                    $this->is_bot_alive = true;
                }
            }

            if ($this->is_bot_alive === false) {
                $this->logController->setCustomLog(
                    $this->server_id,
                    ts3BotLog::STOPPED,
                    'checkBotWork',
                    'Bot is missing on the server. He need a break?',
                );

                ts3ServerConfig::query()
                    ->where('id', '=', $this->server_id)
                    ->update([
                        'bot_status_id'=>ts3BotLog::STOPPED,
                    ]);

                $policeWorkerSetting = ts3BotWorkerPolice::query()
                    ->where('server_id', '=', $this->server_id)
                    ->first(['discord_webhook_url', 'is_discord_webhook_active']);

                if ($policeWorkerSetting->is_discord_webhook_active == true) {
                    $response = Http::post(Crypt::decryptString($policeWorkerSetting->discord_webhook_url), [
                        'content' => $this->qa_name.' is missing on Teamspeak Server. He has probably stopped working',
                        'username' => $this->qa_name.'-Police-Worker',
                    ]);

                    if ($response->status() != 204) {
                        $this->logController->setCustomLog(
                            $this->server_id,
                            ts3BotLog::FAILED,
                            'checkBotAlive',
                            'Webhook could not be send.',
                        );
                    }
                }
            }
        } catch (Exception $e) {
            $this->logController->setCustomLog(
                $this->server_id,
                ts3BotLog::FAILED,
                'Check Bot Keep Alive',
                'There was an error during check bot keep alive',
                $e->getCode(),
                $e->getMessage()
            );

            $this->ts3_VirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
        }
    }

    private function checkBadName(): void
    {
        $badNameController = new BadNameController();

        try {
            //get all clients
            $this->ts3_VirtualServer->clientListReset();
            $clientList = collect($this->ts3_VirtualServer->clientList(['clid']));

            foreach ($clientList->keys()->all() as $clid) {
                //proof only client_type = 0 / 1 = serverquery
                $clidInfo = $this->ts3_VirtualServer->clientGetById($clid);

                if ($clidInfo['client_type'] == 0) {
                    $badNameProofResult = $badNameController->checkBadName($clidInfo['client_nickname']->toString(), $this->server_id);

                    if ($badNameProofResult == true) {
                        //kick client
                        $this->ts3_VirtualServer->clientPoke($clid, 'Your nickname is not allowed on this server!');
                        $this->ts3_VirtualServer->clientKick($clid, TeamSpeak3::KICK_SERVER, 'Your nickname is not allowed on this server!');
                    }
                }
            }
        } catch (Exception $e) {
            $this->logController->setCustomLog(
                $this->server_id,
                ts3BotLog::FAILED,
                'Check Bad Name',
                'There was an error during check bad name',
                $e->getCode(),
                $e->getMessage()
            );

            $this->ts3_VirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
        }
    }
}
