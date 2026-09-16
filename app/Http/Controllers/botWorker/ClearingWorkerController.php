<?php

namespace App\Http\Controllers\botWorker;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sys\TsLogController;
use App\Http\Controllers\tsConfig\tsUriStringHelperController;
use App\Models\tsBot\tsBotLog;
use App\Models\tsBot\tsChannel;
use App\Models\tsBot\tsServerConfig;
use App\Models\tsBotWorkers\tsBotWorkerChannelsCreate;
use App\Models\tsBotWorkers\tsBotWorkerChannelsRemove;
use Exception;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\Adapter;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Node;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

class ClearingWorkerController extends Controller
{
    protected int $server_id;

    protected string $qaName;

    protected Server|Adapter|Host|Node $ts_VirtualServer;

    protected TsLogController $logController;

    public function __construct(int $server_id)
    {
        $this->server_id = $server_id;
        $this->logController = new TsLogController('Clearing-Worker', $this->server_id);
    }

    /**
     * @throws Exception
     */
    public function startClearing(): void
    {
        $tsServerConfig = tsServerConfig::query()
            ->where('id', '=', $this->server_id)->first();

        if ($tsServerConfig->qa_nickname != null) {
            $this->qaName = $tsServerConfig->qa_nickname;
        } else {
            $this->qaName = $tsServerConfig->qa_name;
        }

        $tsUriStringHelper = new tsUriStringHelperController();
        $uri = $tsUriStringHelper->getStandardUriString(
            $tsServerConfig->qa_name,
            $tsServerConfig->qa_pw,
            $tsServerConfig->server_ip,
            $tsServerConfig->server_query_port,
            $tsServerConfig->server_port,
            $this->qaName.'-Clearing-Worker',
            $this->server_id,
        );

        try {
            $this->ts_VirtualServer = TeamSpeak3::factory($uri);
        } catch(Exception $e) {
            $this->logController->setCustomLog(
                $this->server_id,
                tsBotLog::FAILED,
                'Start Clearing-Worker',
                'There was an error while attempting to communicate with the server',
                $e->getCode(),
                $e->getMessage()
            );
        }

        $this->updateChannelList();

        $this->ts_VirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
    }

    /**
     * Synchronize channels between ts server and bot
     * @return void
     */
    private function updateChannelList(): void
    {
        try {
            //get all channels as a collection without SubChannels
            $updateTsChannels = collect($this->ts_VirtualServer->channelList());

            $channelList = [];
            foreach ($updateTsChannels->keys()->all() as $cid) {
                $channelList[] = $cid;
            }

            //get for each key - cid connection the channel info and store in db
            foreach ($updateTsChannels->keys()->all() as $cid) {
                //get channel by id
                $channel = $this->ts_VirtualServer->channelGetById($cid);
                //get channel info
                $channelInfo = $channel->getInfo();
                //update or create channel information
                $this->updateChannelInDatabase($cid, $channelInfo, $channel->toString());
            }

            //get channels where not found at server side and delete in a database
            $deletingChannelList = tsChannel::query()
                ->where('server_id', '=', $this->server_id)
                ->whereNotIn('cid', $channelList)
                ->get();

            //delete channels from db
            foreach ($deletingChannelList as $deleteChannelsFromDB) {
                $this->deleteChannelFromDB($deleteChannelsFromDB->cid);
            }
        } catch(Exception $e) {
            $this->logController->setCustomLog(
                $this->server_id,
                tsBotLog::FAILED,
                'Update Channel List',
                'There was an error during update channel list',
                $e->getCode(),
                $e->getMessage()
            );

            $this->ts_VirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
        }
    }

    private function updateChannelInDatabase(int $cid, array $channelInfo, string $channelName): void
    {
        tsChannel::query()->updateOrCreate(
            [
                'cid'=>$cid,
                'server_id'=>$this->server_id,
            ],
            [
                'pid'=>$channelInfo['pid'],
                'channel_order'=>$channelInfo['channel_order'],
                'channel_name'=>$channelName,
                'channel_topic'=>$channelInfo['channel_topic'],
                'channel_flag_default'=>$channelInfo['channel_flag_default'],
                'channel_flag_password'=>$channelInfo['channel_flag_password'],
                'channel_flag_permanent'=>$channelInfo['channel_flag_permanent'],
                'channel_flag_semi_permanent'=>$channelInfo['channel_flag_semi_permanent'],
                'channel_codec'=>$channelInfo['channel_codec'],
                'channel_codec_quality'=>$channelInfo['channel_codec_quality'],
                'channel_needed_talk_power'=>$channelInfo['channel_needed_talk_power'],
                'channel_icon_id'=>$channelInfo['channel_icon_id'],
                'total_clients_family'=>$channelInfo['total_clients_family'],
                'channel_maxclients'=>$channelInfo['channel_maxclients'],
                'channel_maxfamilyclients'=>$channelInfo['channel_maxfamilyclients'],
                'total_clients'=>$channelInfo['total_clients'],
                'channel_needed_subscribe_power'=>$channelInfo['channel_needed_subscribe_power'],
                'channel_banner_gfx_url'=>$channelInfo['channel_banner_gfx_url'],
                'channel_banner_mode'=>$channelInfo['channel_banner_mode'],
                'channel_description'=>$channelInfo['channel_description'] ?? null,
                'channel_password'=>$channelInfo['channel_password'],
                'channel_codec_latency_factor'=>$channelInfo['channel_codec_latency_factor'],
                'channel_codec_is_unencrypted'=>$channelInfo['channel_codec_is_unencrypted'],
                'channel_security_salt'=>$channelInfo['channel_security_salt'],
                'channel_delete_delay'=>$channelInfo['channel_delete_delay'],
                'channel_unique_identifier'=>$channelInfo['channel_unique_identifier'],
                'channel_flag_maxclients_unlimited'=>$channelInfo['channel_flag_maxclients_unlimited'],
                'channel_flag_maxfamilyclients_unlimited'=>$channelInfo['channel_flag_maxfamilyclients_unlimited'],
                'channel_flag_maxfamilyclients_inherited'=>$channelInfo['channel_flag_maxfamilyclients_inherited'],
                'channel_filepath'=>$channelInfo['channel_filepath'],
                'channel_forced_silence'=>$channelInfo['channel_forced_silence'],
                'channel_name_phonetic'=>$channelInfo['channel_name_phonetic'],
                'seconds_empty'=>$channelInfo['seconds_empty'],
            ]
        );
    }

    private function deleteChannelFromDB(int $cid): void
    {
        tsChannel::query()
            ->where('server_id', '=', $this->server_id)
            ->where('cid', '=', $cid)
            ->delete();

        tsBotWorkerChannelsCreate::query()
            ->where('server_id', '=', $this->server_id)
            ->where('on_cid', '=', $cid)
            ->delete();

        tsBotWorkerChannelsRemove::query()
            ->where('server_id', '=', $this->server_id)
            ->where('channel_cid', '=', $cid)
            ->delete();
    }
}
