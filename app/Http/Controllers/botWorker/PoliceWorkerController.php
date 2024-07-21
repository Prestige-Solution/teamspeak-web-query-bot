<?php

namespace App\Http\Controllers\botWorker;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sys\Ts3LogController;
use App\Http\Controllers\ts3Config\BadNameController;
use App\Http\Controllers\ts3Config\Ts3UriStringHelperController;
use App\Models\ts3Bot\ts3ServerConfig;
use App\Models\ts3BotWorkers\ts3BotWorkerPolice;
use App\Models\ts3BotWorkers\ts3BotWorkerPoliceVpnProtection;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Node;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TeamSpeak3Exception;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\Adapter;

class PoliceWorkerController extends Controller
{
    protected int $serverID;
    protected string $qaName;
    protected Server|Adapter|Host|Node $ts3_VirtualServer;
    protected Ts3LogController $logController;

    /**
     * @param $serverID
     * @return void
     * @throws Exception
     */
    public function startPolice($serverID): void
    {
        //declare
        $this->serverID = $serverID;
        $this->logController = new Ts3LogController('AfkWorker', $this->serverID);

        //get Server config
        $ts3ServerConfig = ts3ServerConfig::query()
            ->where('id','=', $serverID)->first();

        if ($ts3ServerConfig->qa_nickname != NULL)
        {
            $this->qaName = $ts3ServerConfig->qa_nickname;
        }else
        {
            $this->qaName = $ts3ServerConfig->qa_name;
        }

        //get uri with StringHelper
        $ts3StringHelper = new Ts3UriStringHelperController();
        $uri = $ts3StringHelper->getStandardUriString(
            $ts3ServerConfig->qa_name,
            $ts3ServerConfig->qa_pw,
            $ts3ServerConfig->server_ip,
            $ts3ServerConfig->server_query_port,
            $ts3ServerConfig->server_port,
            $this->qaName.'-Police-Worker',
            $ts3ServerConfig->mode,
        );

        //stop if return uri = 0
        if ($uri == 0)
        {
            $this->logController->setCustomLog(
                $this->serverID,
                4,
                'Initialising Clearing Worker',
                'Invalid Server IP Address',
            );

            throw new Exception('Invalid Server IP');
        }

        try
        {
            //initialising ts3 framework
            $TS3PHPFramework = new TeamSpeak3();

            //connect to above specified server
            $this->ts3_VirtualServer = $TS3PHPFramework->factory($uri);

            //policeWorker Settings
            $policeWorkerSetting = ts3BotWorkerPolice::query()->where('server_id','=',$serverID)->first();

            //check VPN
            if($policeWorkerSetting->vpn_protection_active == 1 && config('app.vpn_protection_mail') != false)
            {
                $this->checkVpn($policeWorkerSetting);
            }

            //check bot is working
            $this->checkBotKeepAlive($policeWorkerSetting->check_bot_alive_active == 1);

            //check bad names
            if ($policeWorkerSetting->bad_name_protection_active == true)
            {
                $this->checkBadName();
            }

            //disconnect from server
            $this->ts3_VirtualServer->getAdapter()->getTransport()->disconnect();
        }
        catch(TeamSpeak3Exception | Exception $e)
        {
            //set log
            $this->logController->setLog($e,4,'startPolice');

            //disconnect from server
            $this->ts3_VirtualServer->getAdapter()->getTransport()->disconnect();
        }
    }

    /**
     * @param $policeWorkerSetting
     * @return void
     */
    private function checkVpn($policeWorkerSetting): void
    {
        try
        {
            //api police / max 15 per Minute and 500 per day
            //query Count over all Server
            $apiQueryCountSum = ts3BotWorkerPolice::query()
                ->where('server_id','=',$this->serverID)
                ->sum('vpn_protection_query_count');
            //sum query count per day
            $apiQueryCountPerDaySum = ts3BotWorkerPolice::query()
                ->where('server_id','=',$this->serverID)
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
            if($apiQueryCountPerDaySum <= config('app.vpn_protection_max_query_per_day'))
            {
                //get clients
                $this->ts3_VirtualServer->clientListReset();
                $clientList = collect($this->ts3_VirtualServer->clientList(['clid']));

                foreach ($clientList->keys()->all() as $clid)
                {
                    $clidInfo = $this->ts3_VirtualServer->clientGetById($clid)->getInfo();
                    $clidIP = $clidInfo['connection_client_ip'];
                    $checked = false;
                    $kickResult = false;

                    //check if ip is known
                    $knownIpCheck = ts3BotWorkerPoliceVpnProtection::query()->where('ip_address','=',$clidIP)->first();
                    if($knownIpCheck != NULL && $knownIpCheck->check_result != 'VPN Detection')
                    {
                        $checked = true;

                    }elseif ($knownIpCheck != NULL && $knownIpCheck->check_result == 'VPN Detection')
                    {
                        $kickResult = true;
                        $checked = true;
                    }

                    //ignore own sgid
                    if (Str::contains($clidInfo['client_nickname'],$this->qaName) == true || $clidInfo['client_nickname'] == 'serveradmin')
                    {
                        $kickResult = false;
                        $checked = true;
                    }

                    //ignore allowed sgid
                    $sgids = collect(explode(',',$clidInfo['client_servergroups']));
                    foreach ($sgids as $sgid)
                    {
                        if($policeWorkerSetting->allow_sgid_vpn == $sgid)
                        {
                            $kickResult = false;
                            $checked = true;
                        }
                    }

                    //api checks available
                    if($apiQueryCountSum <= $apiQueryMaxCount && config('app.vpn_protection_mail') != false && $checked == false && Carbon::now() >= $policeWorkerSetting->vpn_protection_next_check_available)
                    {
                        //api www.getipintel.net/free-proxy-vpn-tor-detection-api
                        $checkIP = Http::get('http://check.getipintel.net/check.php?ip='.$clidIP.'&contact='.config('app.vpn_protection_mail').'&flags=m&format=json');

                        if ($checkIP->status() == 200)
                        {
                            $checkIPDecode = $checkIP->json();
                            if($checkIPDecode['result'] != 0)
                            {
                                //kick is true
                                $kickResult = true;
                                //store ip with check_result = vpn
                                ts3BotWorkerPoliceVpnProtection::query()->updateOrCreate(
                                    [
                                        'server_id'=>$this->serverID,
                                        'ip_address'=>$clidIP,
                                    ],
                                    [
                                        'check_result'=>'VPN Detection',
                                    ]
                                );
                            }else
                            {
                                ts3BotWorkerPoliceVpnProtection::query()->updateOrCreate(
                                    [
                                        'server_id'=>$this->serverID,
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
                            if($apiQueryCountSum >= $apiQueryMaxCount)
                            {
                                ts3BotWorkerPolice::query()->where('server_id','=',$this->serverID)->update([
                                    'vpn_protection_next_check_available'=>Carbon::now()->addMinutes(15),
                                ]);
                            }
                        }
                    }
                    if($kickResult == true)
                    {
                        $this->ts3_VirtualServer->clientPoke($clid,"VPN wurde erkannt. Bitte abschalten oder im Discord melden");
                        $this->ts3_VirtualServer->clientKick($clid,TeamSpeak3::KICK_SERVER,"VPN wurde erkannt. Bitte abschalten oder im Discord melden");
                    }
                }

                //set query request count
                ts3BotWorkerPolice::query()->where('server_id','=',$this->serverID)->update([
                    'vpn_protection_query_count'=>$apiQueryCountServer,
                    'vpn_protection_query_per_day'=>$apiQueryCountPerDay + $apiQueryCountThisProcess,
                ]);
            }
        }catch (TeamSpeak3Exception $e)
        {
            //set log
            $this->logController->setLog($e,4,'checkVPN');
        }
    }

    private function checkBotKeepAlive($checkKeepAlive): void
    {
        try
        {
            $botIsAlive = false;

            //client list
            $checkBotIsWorking = collect($this->ts3_VirtualServer->clientList(['client_nickname'=>$this->qaName]));

            foreach ($checkBotIsWorking->keys()->all() as $clid)
            {
                $BotQueryName = $this->ts3_VirtualServer->clientGetById($clid);

                if ($BotQueryName['client_nickname'] == $this->qaName)
                {
                    $botIsAlive = true;
                }
            }

            if ($botIsAlive == false)
            {
                //set custom log
                $this->logController->setCustomLog(
                    $this->serverID,
                    3,
                    'checkBotWork',
                    'Bot is missing on the server. He need a break?',
                    null,
                    null,
                );
                //set status id to 3
                ts3ServerConfig::query()
                    ->where('id','=',$this->serverID)
                    ->update([
                        'bot_status_id'=>3,
                    ]);

                if ($checkKeepAlive == 1)
                {
                    //send Discord
                    $policeWorkerSetting = ts3BotWorkerPolice::query()
                        ->where('server_id','=',$this->serverID)
                        ->first(['discord_webhook', 'discord_webhook_active']);

                    if($policeWorkerSetting->discord_webhook_active == true)
                    {
                        $message = json_encode([
                            "content" => $this->qaName." wird auf dem Teamspeak vermisst. Er hat wohl seine Arbeit eingestellt.",
                            "username" => $this->qaName."-Police-Worker",

                        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

                        $send = curl_init(Crypt::decryptString($policeWorkerSetting->discord_webhook));

                        curl_setopt($send, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
                        curl_setopt($send, CURLOPT_POST, 1);
                        curl_setopt($send, CURLOPT_POSTFIELDS, $message);
                        curl_setopt($send, CURLOPT_FOLLOWLOCATION, 1);
                        curl_setopt($send, CURLOPT_HEADER, 0);
                        curl_setopt($send, CURLOPT_RETURNTRANSFER, 1);

                        curl_exec($send);
                    }
                }
            }
        }catch (TeamSpeak3Exception $e)
        {
            //set log
            $this->logController->setLog($e,4,'checkBotKeepAlive');
        }
    }

    private function checkBadName(): void
    {
        //declare badNameController
        $badNameController = new BadNameController();

        try {
            //get all clients
            $this->ts3_VirtualServer->clientListReset();
            $clientList = collect($this->ts3_VirtualServer->clientList(['clid']));

            foreach ($clientList->keys()->all() as $clid)
            {
                //proof only client_type = 0 / 1 = serverquery
                $clidInfo = $this->ts3_VirtualServer->clientGetById($clid);

                if ($clidInfo['client_type'] == 0)
                {
                    $badNameProofResult = $badNameController->checkBadName($clidInfo['client_nickname']->toString(),$this->serverID);

                    if ($badNameProofResult == true)
                    {
                        //kick client
                        $this->ts3_VirtualServer->clientPoke($clid,"Dein Nickname ist auf diesen Server nicht erlaubt!");
                        $this->ts3_VirtualServer->clientKick($clid,TeamSpeak3::KICK_SERVER,"BadName Protection");
                    }
                }
            }
        }catch (TeamSpeak3Exception $e)
        {
            //set log
            $this->logController->setLog($e,4,'checkBadName');
        }
    }
}
