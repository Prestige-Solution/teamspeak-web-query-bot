<?php

namespace App\Http\Controllers\botWorker;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sys\Ts3LogController;
use App\Models\ts3Bot\ts3ServerConfig;
use App\Models\ts3BotWorkers\ts3BotWorkerAfk;
use Exception;
use Illuminate\Support\Facades\Crypt;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TeamSpeak3Exception;

class AfkWorkerController extends Controller
{
    protected int $serverID;
    protected Server $ts3_VirtualServer;
    protected Ts3LogController $logController;

    /**
     * @param $serverID
     * @return void
     */
    public function afkMoverWorker($serverID): void
    {
        //declare variables
        $this->serverID = $serverID;
        $this->logController = new Ts3LogController('AfkWorker', $this->serverID);

        try {
            //get Server config
            $ts3ServerConfig = ts3ServerConfig::query()
                ->where('id', '=', $this->serverID)->first();

            if ($ts3ServerConfig->qa_nickname != null)
            {
                $qaNickname = $ts3ServerConfig->qa_nickname;
            }else
            {
                $qaNickname = $ts3ServerConfig->qa_name;
            }

            //declare class
            $TS3PHPFramework = new TeamSpeak3();

            // Connect via ipv4 to ts3 Server // timeout in seconds
            $uri = 'serverquery://'
                .$ts3ServerConfig->qa_name.':'.Crypt::decryptString($ts3ServerConfig->qa_pw).
                '@'.$ts3ServerConfig->ipv4.
                ':'.$ts3ServerConfig->server_query_port.
                '/?server_port='.$ts3ServerConfig->server_port.
                '&blocking=0'.
                '&no_query_clients'.
                '&nickname='.$qaNickname.'-AFK-Worker';

            // connect to above specified server, authenticate and spawn an object for the virtual server on port 9987
            $this->ts3_VirtualServer = $TS3PHPFramework->factory($uri);

            //proof if active
            $functionIsActive = ts3BotWorkerAfk::query()->where('server_id','=',$this->serverID)->first(['active','afk_kicker_active']);

            //afk mover is active
            if ($functionIsActive->active == true)
            {
                $this->afkMover();
            }

            //afk kicker is active
            if($functionIsActive->afk_kicker_active == true)
            {
                $this->afkKicker();
            }

        }catch(TeamSpeak3Exception | Exception $e)
        {
            // set log
            $this->logController->setLog($e,4);
        }
    }

    private function afkMover(): void
    {
        try
        {
            //get all Clients on server
            $allClientsOnServer = collect($this->ts3_VirtualServer->clientList());
            //has worker excluded servergroups
            $afkWorkerExcludedServerGroups = ts3BotWorkerAfk::query()->where('server_id','=',$this->serverID)->get();
            $afkWorkerConfig = ts3BotWorkerAfk::query()->where('server_id','=',$this->serverID)->first();
            //get worker config
            $maxIdleTime = $afkWorkerConfig->max_client_idle_time;
            $afkChannelCid = $afkWorkerConfig->afk_channel_cid;

            foreach ($allClientsOnServer as $client)
            {
                //get Client info
                $clientClid = $client['clid'];
                $clientIdleTime = $client['client_idle_time'];
                $clientServerGroups = collect(explode(',',$client['client_servergroups']));
                $clientChannelCid = $client['cid'];

                $excludedServerGroupExistsResult = false;

                foreach ($afkWorkerExcludedServerGroups as $afkWorkerExcludedServerGroup)
                {
                    //has client excluded serverGroups
                    foreach ($clientServerGroups as $serverGroup)
                    {
                        if ($serverGroup == $afkWorkerExcludedServerGroup->excluded_servergroup)
                        {
                            $excludedServerGroupExistsResult = true;
                        }
                    }
                }
                //has no excluded groups
                if ($excludedServerGroupExistsResult == false && $afkChannelCid != $clientChannelCid)
                {
                    //if max idle time reached
                    if ($clientIdleTime > $maxIdleTime) {
                        $this->ts3_VirtualServer->clientMove($clientClid, $afkChannelCid);
                    }
                }
            }
        }catch(Exception $e)
        {
            //set log
            $this->logController->setLog($e,4,'afkMover');
        }
    }

    private function afkKicker(): void
    {
        try
        {
            //get all Clients on server
            $allClientsOnServer = collect($this->ts3_VirtualServer->clientList());
            //has worker excluded servergroups
            $afkWorkerExcludedServerGroups = ts3BotWorkerAfk::query()->where('server_id','=',$this->serverID)->get();
            $afkWorkerConfig = ts3BotWorkerAfk::query()->where('server_id','=',$this->serverID)->first();
            //get worker config
            $afkKickerMaxIdleTime = $afkWorkerConfig->afk_kicker_max_idle_time;
            //serverSlots
            $serverClientsOnline = $this->ts3_VirtualServer->getProperty('virtualserver_clientsonline');

            foreach ($allClientsOnServer as $client)
            {
                //get Client info
                $clientClid = $client['clid'];
                $clientIdleTime = $client['client_idle_time'];
                $clientServerGroups = collect(explode(',',$client['client_servergroups']));

                $excludedServerGroupExistsResult = false;

                foreach ($afkWorkerExcludedServerGroups as $afkWorkerExcludedServerGroup)
                {
                    //has client excluded serverGroups
                    foreach ($clientServerGroups as $serverGroup)
                    {
                        if ($serverGroup == $afkWorkerExcludedServerGroup->excluded_servergroup)
                        {
                            $excludedServerGroupExistsResult = true;
                        }
                    }
                }
                //has no excluded groups
                if ($excludedServerGroupExistsResult == false)
                {
                    //if 0 = kick without slots proofing else slot proofing
                    if($afkWorkerConfig->afk_kicker_slots_online == 0)
                    {
                        $kickMsg = 'Du hast die maximale Inaktivität erreicht und wurdest vom Server gekickt!';
                        //if max idle time reached
                        if ($clientIdleTime > $afkKickerMaxIdleTime) {
                            $this->ts3_VirtualServer->clientKick($clientClid,TeamSpeak3::KICK_SERVER,$kickMsg);
                        }
                    }else
                    {
                        if ($serverClientsOnline > $afkWorkerConfig->afk_kicker_slots_online)
                        {
                            $kickMsg = 'Tut uns leid, aber der Server wird langsam voll!';
                            $this->ts3_VirtualServer->clientKick($clientClid,TeamSpeak3::KICK_SERVER, $kickMsg);

                            //decrease online counter
                            $serverClientsOnline = $serverClientsOnline - 1;
                        }
                    }
                }
            }
        }catch(Exception $e)
        {
            //set log
            $this->logController->setLog($e,4,'afkKicker');
        }
    }
}
