<?php

namespace App\Http\Controllers\botWorker;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sys\Ts3LogController;
use App\Http\Controllers\ts3Config\Ts3UriStringHelperController;
use App\Models\ts3Bot\ts3ServerConfig;
use App\Models\ts3BotWorkers\ts3BotWorkerChannelRemover;
use Exception;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\Adapter;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Node;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TeamSpeak3Exception;

class ChannelRemoveWorkerController extends Controller
{
    protected int $serverID;
    protected Ts3LogController $logController;
    protected string $qaName;
    protected Server|Adapter|Host|Node $ts3_VirtualServer;

    public function startChannelRemover($serverID): void
    {
        //declare
        $this->serverID = $serverID;
        $this->logController = new Ts3LogController('ChannelRemoverWorker', $this->serverID);

        try {
            //get Server config
            $ts3ServerConfig = ts3ServerConfig::query()
                ->where('id', '=', $serverID)->first();

            if ($ts3ServerConfig->qa_nickname != null)
            {
                $this->qaName = $ts3ServerConfig->qa_nickname;
            } else
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

            // connect to the server
            $this->ts3_VirtualServer = TeamSpeak3::factory($uri);

            //get sub-channels
            $subChannelRemoves = ts3BotWorkerChannelRemover::query()
                ->where('server_id','=',$this->serverID)
                ->where('active','=',1)
                ->get();

            foreach ($subChannelRemoves as $subChannelRemove)
            {
                //get sub-channel list
                $subChannels = collect($this->ts3_VirtualServer->channelList(['pid'=>$subChannelRemove->channel_cid]));

                //proof delete time
                foreach ($subChannels->keys()->all() as $subChannel)
                {
                    $subChannelInfo = $this->ts3_VirtualServer->channelGetById($subChannel)->getInfo();

                    if($subChannelInfo['seconds_empty'] >= $subChannelRemove->channel_max_seconds_empty)
                    {
                        $this->ts3_VirtualServer->channelDelete($subChannel);
                    }
                }
            }

            //disconnect from server
            $this->ts3_VirtualServer->getAdapter()->getTransport()->disconnect();
        }
        catch(TeamSpeak3Exception | Exception $e)
        {
            //set log
            $this->logController->setLog($e->getMessage(),4,'startChannelRemover');

            //disconnect from server
            $this->ts3_VirtualServer->getAdapter()->getTransport()->disconnect();
        }
    }
}
