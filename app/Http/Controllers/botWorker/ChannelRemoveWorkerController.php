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

class ChannelRemoveWorkerController extends Controller
{
    protected int $server_id;

    protected TsLogController $logController;

    protected string $qa_name;

    protected Server|Adapter|Host|Node $ts_VirtualServer;

    public function __construct(int $server_id)
    {
        $this->server_id = $server_id;
        $this->logController = new TsLogController('Channel-Remover-Worker', $this->server_id);
    }

    /**
     * create new bot instance to handle remove channel jobs
     * @throws Exception
     */
    public function channelRemoverWorker(): void
    {
        try {
            $tsServerConfig = tsServerConfig::query()
                ->where('id', '=', $this->server_id)->first();

            if ($tsServerConfig->qa_nickname != null) {
                $this->qa_name = $tsServerConfig->qa_nickname;
            } else {
                $this->qa_name = $tsServerConfig->qa_name;
            }

            $tsStringHelper = new TsUriStringHelperController();
            $uri = $tsStringHelper->getStandardUriString(
                $tsServerConfig->qa_name,
                $tsServerConfig->qa_pw,
                $tsServerConfig->server_ip,
                $tsServerConfig->server_query_port,
                $tsServerConfig->server_port,
                $this->qa_name.'-Remover-Worker',
                $this->server_id,
            );

            $this->ts_VirtualServer = TeamSpeak3::factory($uri);
        } catch(Exception $e) {
            $this->logController->setCustomLog($this->server_id,
                tsBotLog::FAILED,
                'Start Channel-Remover-Worker',
                'There was an error while attempting to communicate with the server',
                $e->getCode(),
                $e->getMessage()
            );
        }

        $this->channelRemover();
        $this->ts_VirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
    }

    /**
     * task channel remover
     */
    private function channelRemover(): void
    {
        try {
            //get sub-channels
            $subChannelRemoves = tsBotWorkerChannelsRemove::query()
                ->where('server_id', '=', $this->server_id)
                ->where('is_active', '=', true)
                ->get();

            foreach ($subChannelRemoves as $subChannelRemove) {
                //get sub-channel list
                $subChannels = collect($this->ts_VirtualServer->channelList(['pid'=>$subChannelRemove->channel_cid]));

                //proof delete time
                foreach ($subChannels->keys()->all() as $subChannel) {
                    $subChannelInfo = $this->ts_VirtualServer->channelGetById($subChannel)->getInfo();

                    //seconds = -1 means the channel is currently in use
                    if ($subChannelInfo['seconds_empty'] != '-1' && $subChannelInfo['seconds_empty'] >= $subChannelRemove->channel_max_seconds_empty) {
                        $this->ts_VirtualServer->channelDelete($subChannel);

                        tsChannel::query()
                            ->where('server_id', '=', $this->server_id)
                            ->where('cid', '=', $subChannelInfo['cid'])
                            ->delete();
                    }
                }
            }

            //update column updated_at
            tsBotWorkerChannelsRemove::query()->where('server_id', '=', $this->server_id)->touch();
        } catch (Exception $e) {
            $this->logController->setCustomLog(
                $this->server_id,
                tsBotLog::FAILED,
                'Channel-Remover',
                'There was an error during channel remover',
                $e->getCode(),
                $e->getMessage()
            );

            $this->ts_VirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
        }
    }
}
