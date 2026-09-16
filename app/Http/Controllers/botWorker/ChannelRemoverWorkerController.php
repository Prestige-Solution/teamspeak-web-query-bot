<?php

namespace App\Http\Controllers\botWorker;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sys\TsLogController;
use App\Http\Controllers\tsConfig\TsUriStringHelperController;
use App\Models\tsBot\tsBotLog;
use App\Models\tsBot\tsChannel;
use App\Models\tsBot\tsServerConfig;
use App\Models\tsBotWorkers\tsBotWorkerChannelsRemove;
use Exception;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\Adapter;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Node;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

class ChannelRemoverWorkerController extends Controller
{
    protected int $serverId;

    protected TsLogController $logController;

    protected string $qaName;

    protected Server|Adapter|Host|Node $tsVirtualServer;

    public function __construct(int $serverId)
    {
        $this->serverId = $serverId;
        $this->logController = new TsLogController('Channel-Remover-Worker', $this->serverId);
    }

    /**
     * create new bot instance to handle remove channel jobs
     * @throws Exception
     */
    public function channelRemoverWorker(): void
    {
        try {
            $tsServerConfig = tsServerConfig::query()
                ->where('id', '=', $this->serverId)->first();

            if ($tsServerConfig->qa_nickname != null) {
                $this->qaName = $tsServerConfig->qa_nickname;
            } else {
                $this->qaName = $tsServerConfig->qa_name;
            }

            $tsStringHelper = new TsUriStringHelperController();
            $uri = $tsStringHelper->getStandardUriString(
                $tsServerConfig->qa_name,
                $tsServerConfig->qa_pw,
                $tsServerConfig->server_ip,
                $tsServerConfig->server_query_port,
                $tsServerConfig->server_port,
                $this->qaName.'-Remover-Worker',
                $this->serverId,
            );

            $this->tsVirtualServer = TeamSpeak3::factory($uri);
        } catch(Exception $e) {
            $this->logController->setCustomLog($this->serverId,
                tsBotLog::FAILED,
                'Start Channel-Remover-Worker',
                'There was an error while attempting to communicate with the server',
                $e->getCode(),
                $e->getMessage()
            );

            return;
        }

        $this->channelRemover();
        $this->tsVirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
    }

    /**
     * task channel remover
     */
    private function channelRemover(): void
    {
        try {
            //get sub-channels
            $subChannelRemoves = tsBotWorkerChannelsRemove::query()
                ->where('server_id', '=', $this->serverId)
                ->where('is_active', '=', true)
                ->get();

            foreach ($subChannelRemoves as $subChannelRemove) {
                //get sub-channel list
                $subChannels = collect($this->tsVirtualServer->channelList(['pid'=>$subChannelRemove->channel_cid]));

                //proof delete time
                foreach ($subChannels->keys()->all() as $subChannel) {
                    $subChannelInfo = $this->tsVirtualServer->channelGetById($subChannel)->getInfo();

                    //seconds = -1 means the channel is currently in use
                    if ($subChannelInfo['seconds_empty'] != '-1' && $subChannelInfo['seconds_empty'] >= $subChannelRemove->channel_max_seconds_empty) {
                        $this->tsVirtualServer->channelDelete($subChannel);

                        tsChannel::query()
                            ->where('server_id', '=', $this->serverId)
                            ->where('cid', '=', $subChannelInfo['cid'])
                            ->delete();
                    }
                }
            }

            //update column updated_at
            tsBotWorkerChannelsRemove::query()->where('server_id', '=', $this->serverId)->touch();
        } catch (Exception $e) {
            $this->logController->setCustomLog(
                $this->serverId,
                tsBotLog::FAILED,
                'Channel-Remover',
                'There was an error during channel remover',
                $e->getCode(),
                $e->getMessage()
            );

            $this->tsVirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
        }
    }
}
