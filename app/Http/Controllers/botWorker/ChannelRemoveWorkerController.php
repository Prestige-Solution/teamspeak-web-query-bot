<?php

namespace App\Http\Controllers\botWorker;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sys\Ts3LogController;
use App\Http\Controllers\ts3Config\Ts3UriStringHelperController;
use App\Models\ts3Bot\ts3BotLog;
use App\Models\ts3Bot\ts3Channel;
use App\Models\ts3Bot\ts3ServerConfig;
use App\Models\ts3BotWorkers\ts3BotWorkerChannelsRemove;
use Exception;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\Adapter;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TeamSpeak3Exception;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Node;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

class ChannelRemoveWorkerController extends Controller
{
    protected int $server_id;

    protected Ts3LogController $logController;

    protected string $qa_name;

    protected Server|Adapter|Host|Node $ts3_VirtualServer;

    public function __construct(int $server_id)
    {
        $this->server_id = $server_id;
        $this->logController = new Ts3LogController('Channel-Remover-Worker', $this->server_id);
    }

    /**
     * create new bot instance to handle remove channel jobs
     * @throws Exception
     */
    public function channelRemoverWorker(): void
    {
        try {
            $ts3ServerConfig = ts3ServerConfig::query()
                ->where('id', '=', $this->server_id)->first();

            if ($ts3ServerConfig->qa_nickname != null) {
                $this->qa_name = $ts3ServerConfig->qa_nickname;
            } else {
                $this->qa_name = $ts3ServerConfig->qa_name;
            }

            $ts3StringHelper = new Ts3UriStringHelperController();
            $uri = $ts3StringHelper->getStandardUriString(
                $ts3ServerConfig->qa_name,
                $ts3ServerConfig->qa_pw,
                $ts3ServerConfig->server_ip,
                $ts3ServerConfig->server_query_port,
                $ts3ServerConfig->server_port,
                $this->qa_name.'-Remover-Worker',
                $this->server_id,
                $ts3ServerConfig->mode
            );

            $this->ts3_VirtualServer = TeamSpeak3::factory($uri);

        } catch(TeamSpeak3Exception $e) {
            $this->logController->setLog($e, ts3BotLog::FAILED, 'Start Channel-Remover-Worker');
            $this->ts3_VirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
        }

        $this->channelRemover();
        $this->ts3_VirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
    }

    /**
     * task channel remover
     */
    private function channelRemover(): void
    {
        try {
            //get sub-channels
            $subChannelRemoves = ts3BotWorkerChannelsRemove::query()
                ->where('server_id', '=', $this->server_id)
                ->where('is_active', '=', true)
                ->get();

            foreach ($subChannelRemoves as $subChannelRemove) {
                //get sub-channel list
                $subChannels = collect($this->ts3_VirtualServer->channelList(['pid'=>$subChannelRemove->channel_cid]));

                //proof delete time
                foreach ($subChannels->keys()->all() as $subChannel) {
                    $subChannelInfo = $this->ts3_VirtualServer->channelGetById($subChannel)->getInfo();

                    if ($subChannelInfo['seconds_empty'] >= $subChannelRemove->channel_max_seconds_empty) {
                        $this->ts3_VirtualServer->channelDelete($subChannel);

                        ts3Channel::query()
                            ->where('server_id', '=', $this->server_id)
                            ->where('cid', '=', $subChannelInfo['cid'])
                            ->delete();
                    }
                }
            }

            //update column updated_at
            ts3BotWorkerChannelsRemove::query()->touch();

        } catch (TeamSpeak3Exception $e) {
            $this->logController->setLog($e, ts3BotLog::FAILED, 'Channel-Remover');
            //disconnect from server
            $this->ts3_VirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
        }
    }
}
