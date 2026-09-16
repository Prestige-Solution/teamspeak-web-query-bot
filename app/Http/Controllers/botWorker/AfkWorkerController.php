<?php

namespace App\Http\Controllers\botWorker;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sys\TsLogController;
use App\Http\Controllers\tsConfig\TsUriStringHelperController;
use App\Models\tsBot\tsBotLog;
use App\Models\tsBot\tsServerConfig;
use App\Models\tsBotWorkers\tsBotWorkerAfk;
use Exception;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\Adapter;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Node;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

class AfkWorkerController extends Controller
{
    protected int $serverId;

    protected string $qaName;

    protected TsLogController $logController;

    protected Server|Adapter|Host|Node $tsVirtualServer;

    public function __construct(int $serverId)
    {
        $this->serverId = $serverId;
        $this->logController = new TsLogController('Afk-Worker', $this->serverId);
    }

    /**
     * create a new bot instance to handle afk jobs
     * @throws Exception
     */
    public function afkMoverWorker(): void
    {
        try {
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
                $this->qaName.'-AFK-Worker',
                $this->serverId,
            );

            $this->tsVirtualServer = TeamSpeak3::factory($uri);
        } catch(Exception $e) {
            $this->logController->setCustomLog(
                $this->serverId,
                tsBotLog::FAILED,
                'AFK-Worker',
                'There was an error while attempting to communicate with the server',
                $e->getCode(),
                $e->getMessage()
            );

            return;
        }

        $functionIsActive = tsBotWorkerAfk::query()->where('server_id', '=', $this->serverId);
        if ($functionIsActive->count() > 0 && $functionIsActive->first()->is_afk_active == true) {
            $this->afkMover();
        }

        if ($functionIsActive->count() > 0 && $functionIsActive->first()->is_afk_kicker_active == true) {
            $this->afkKicker();
        }

        $this->tsVirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
    }

    /**
     * handle afk clients
     */
    private function afkMover(): void
    {
        try {
            //get all Clients on server
            $allClientsOnServer = collect($this->tsVirtualServer->clientList());
            //has worker excluded servergroups
            $afkWorkerExcludedServerGroups = tsBotWorkerAfk::query()->where('server_id', '=', $this->serverId)->get();
            $afkWorkerConfig = tsBotWorkerAfk::query()->where('server_id', '=', $this->serverId)->first();
            //get worker config
            $maxIdleTime = $afkWorkerConfig->max_client_idle_time;
            $afkChannelCid = $afkWorkerConfig->afk_channel_cid;

            foreach ($allClientsOnServer as $client) {
                //get Client info
                $clientClid = $client['clid'];
                $clientIdleTime = $client['client_idle_time'];
                $clientServerGroups = collect(explode(',', $client['client_servergroups']));
                $clientChannelCid = $client['cid'];

                $excludedServerGroupExistsResult = false;

                foreach ($afkWorkerExcludedServerGroups as $afkWorkerExcludedServerGroup) {
                    //has client excluded serverGroups
                    foreach ($clientServerGroups as $serverGroup) {
                        if ($serverGroup == $afkWorkerExcludedServerGroup->excluded_servergroup) {
                            $excludedServerGroupExistsResult = true;
                        }
                    }
                }
                //has no excluded groups
                if ($excludedServerGroupExistsResult == false && $afkChannelCid != $clientChannelCid) {
                    //if max idle time reached
                    if ($clientIdleTime > $maxIdleTime) {
                        $this->tsVirtualServer->clientMove($clientClid, $afkChannelCid);
                    }
                }
            }
        } catch(Exception $e) {
            $this->logController->setCustomLog(
                $this->serverId,
                tsBotLog::FAILED,
                'Afk-Mover',
                'There was an error during afk mover.',
                $e->getCode(),
                $e->getMessage()
            );
            $this->tsVirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
        }
    }

    /**
     * kick afk clients
     */
    private function afkKicker(): void
    {
        try {
            //get all Clients on server
            $allClientsOnServer = collect($this->tsVirtualServer->clientList());
            //has worker excluded servergroups
            $afkWorkerExcludedServerGroups = tsBotWorkerAfk::query()->where('server_id', '=', $this->serverId)->get();
            $afkWorkerConfig = tsBotWorkerAfk::query()->where('server_id', '=', $this->serverId)->first();
            //get worker config
            $afkKickerMaxIdleTime = $afkWorkerConfig->afk_kicker_max_idle_time;
            //serverSlots
            $serverClientsOnline = $this->tsVirtualServer->getProperty('virtualserver_clientsonline');

            foreach ($allClientsOnServer as $client) {
                //get Client info
                $clientClid = $client['clid'];
                $clientIdleTime = $client['client_idle_time'];
                $clientServerGroups = collect(explode(',', $client['client_servergroups']));

                $excludedServerGroupExistsResult = false;

                foreach ($afkWorkerExcludedServerGroups as $afkWorkerExcludedServerGroup) {
                    //has client excluded serverGroups
                    foreach ($clientServerGroups as $serverGroup) {
                        if ($serverGroup == $afkWorkerExcludedServerGroup->excluded_servergroup) {
                            $excludedServerGroupExistsResult = true;
                        }
                    }
                }
                //has no excluded groups
                if ($excludedServerGroupExistsResult == false) {
                    //if 0 = kick without slots proofing else slot proofing
                    if ($afkWorkerConfig->afk_kicker_slots_online == 0) {
                        $kickMsg = 'You have reached maximum inactivity and have been kicked from the server!';
                        //if max idle time reached
                        if ($clientIdleTime > $afkKickerMaxIdleTime) {
                            $this->tsVirtualServer->clientKick($clientClid, TeamSpeak3::KICK_SERVER, $kickMsg);
                        }
                    } else {
                        if ($serverClientsOnline > $afkWorkerConfig->afk_kicker_slots_online) {
                            $kickMsg = 'Sorry, but the server is getting full!';
                            $this->tsVirtualServer->clientKick($clientClid, TeamSpeak3::KICK_SERVER, $kickMsg);

                            //decrease online counter
                            $serverClientsOnline = $serverClientsOnline - 1;
                        }
                    }
                }
            }
        } catch(Exception $e) {
            $this->logController->setCustomLog(
                $this->serverId,
                tsBotLog::FAILED,
                'Afk-Kicker',
                'There was an error during afk kicker',
                $e->getCode(),
                $e->getMessage()
            );
            $this->tsVirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
        }
    }
}
