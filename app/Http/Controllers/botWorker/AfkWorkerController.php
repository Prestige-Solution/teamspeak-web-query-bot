<?php

namespace App\Http\Controllers\botWorker;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sys\Ts3LogController;
use App\Http\Controllers\ts3Config\Ts3UriStringHelperController;
use App\Models\ts3Bot\ts3ServerConfig;
use App\Models\ts3BotWorkers\ts3BotWorkerAfk;
use Exception;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\Adapter;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Node;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TeamSpeak3Exception;

class AfkWorkerController extends Controller
{
    protected int $serverID;
    protected string $qaName;
    protected Ts3LogController $logController;
    protected Server|Adapter|Host|Node $ts3_VirtualServer;

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
                $this->qaName.'-Remover-Worker',
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

            // connect to above specified server, authenticate and spawn an object for the virtual server
            $this->ts3_VirtualServer = TeamSpeak3::factory($uri);

            //proof if active
            $functionIsActive = ts3BotWorkerAfk::query()->where('server_id','=',$this->serverID);

            //afk mover is active
            if ($functionIsActive->count() > 0 && $functionIsActive->first(['active'])->active == true)
            {
                $this->afkMover();
            }

            //afk kicker is active
            if($functionIsActive->count() > 0 && $functionIsActive->first(['afk_kicker_active'])->afk_kicker_active == true)
            {
                $this->afkKicker();
            }

            //disconnect from server
            $this->ts3_VirtualServer->getAdapter()->getTransport()->disconnect();

        }catch(TeamSpeak3Exception | Exception $e)
        {
            // set log
            $this->logController->setLog($e->getMessage(),4, 'AFK Worker');

            //disconnect from server
            $this->ts3_VirtualServer->getAdapter()->getTransport()->disconnect();
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
