<?php

namespace App\Http\Controllers\botWorker;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sys\Ts3LogController;
use App\Http\Controllers\ts3Config\Ts3UriStringHelperController;
use App\Models\ts3Bot\ts3BotLog;
use App\Models\ts3Bot\ts3ServerConfig;
use App\Models\ts3BotWorkers\ts3BotWorkerAfk;
use Exception;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\Adapter;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Node;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

class AfkWorkerController extends Controller
{
    protected int $server_id;

    protected string $qa_name;

    protected Ts3LogController $logController;

    protected Server|Adapter|Host|Node $ts3_VirtualServer;

    public function __construct(int $server_id)
    {
        $this->server_id = $server_id;
        $this->logController = new Ts3LogController('Afk-Worker', $this->server_id);
    }

    /**
     * create a new bot instance to handle afk jobs
     * @throws Exception
     */
    public function afkMoverWorker(): void
    {
        try {
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
                $this->qa_name.'-AFK-Worker',
                $this->server_id,
                $ts3ServerConfig->mode
            );

            $this->ts3_VirtualServer = TeamSpeak3::factory($uri);
        } catch(Exception $e) {
            $this->logController->setCustomLog(
                $this->server_id,
                ts3BotLog::FAILED,
                'AFK-Worker',
                'There was an error while attempting to communicate with the server',
                $e->getCode(),
                $e->getMessage()
            );
        }

        $functionIsActive = ts3BotWorkerAfk::query()->where('server_id', '=', $this->server_id);
        if ($functionIsActive->count() > 0 && $functionIsActive->first()->is_afk_active == true) {
            $this->afkMover();
        }

        if ($functionIsActive->count() > 0 && $functionIsActive->first()->is_afk_kicker_active == true) {
            $this->afkKicker();
        }

        $this->ts3_VirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
    }

    /**
     * handle afk clients
     */
    private function afkMover(): void
    {
        try {
            //get all Clients on server
            $allClientsOnServer = collect($this->ts3_VirtualServer->clientList());
            //has worker excluded servergroups
            $afkWorkerExcludedServerGroups = ts3BotWorkerAfk::query()->where('server_id', '=', $this->server_id)->get();
            $afkWorkerConfig = ts3BotWorkerAfk::query()->where('server_id', '=', $this->server_id)->first();
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
                        $this->ts3_VirtualServer->clientMove($clientClid, $afkChannelCid);
                    }
                }
            }
        } catch(Exception $e) {
            $this->logController->setCustomLog(
                $this->server_id,
                ts3BotLog::FAILED,
                'Afk-Mover',
                'There was an error during afk mover.',
                $e->getCode(),
                $e->getMessage()
            );
            $this->ts3_VirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
        }
    }

    /**
     * kick afk clients
     */
    private function afkKicker(): void
    {
        try {
            //get all Clients on server
            $allClientsOnServer = collect($this->ts3_VirtualServer->clientList());
            //has worker excluded servergroups
            $afkWorkerExcludedServerGroups = ts3BotWorkerAfk::query()->where('server_id', '=', $this->server_id)->get();
            $afkWorkerConfig = ts3BotWorkerAfk::query()->where('server_id', '=', $this->server_id)->first();
            //get worker config
            $afkKickerMaxIdleTime = $afkWorkerConfig->afk_kicker_max_idle_time;
            //serverSlots
            $serverClientsOnline = $this->ts3_VirtualServer->getProperty('virtualserver_clientsonline');

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
                            $this->ts3_VirtualServer->clientKick($clientClid, TeamSpeak3::KICK_SERVER, $kickMsg);
                        }
                    } else {
                        if ($serverClientsOnline > $afkWorkerConfig->afk_kicker_slots_online) {
                            $kickMsg = 'Sorry, but the server is getting full!';
                            $this->ts3_VirtualServer->clientKick($clientClid, TeamSpeak3::KICK_SERVER, $kickMsg);

                            //decrease online counter
                            $serverClientsOnline = $serverClientsOnline - 1;
                        }
                    }
                }
            }
        } catch(Exception $e) {
            $this->logController->setCustomLog(
                $this->server_id,
                ts3BotLog::FAILED,
                'Afk-Kicker',
                'There was an error during afk kicker',
                $e->getCode(),
                $e->getMessage()
            );
            $this->ts3_VirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
        }
    }
}
