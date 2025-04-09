<?php

namespace App\Http\Controllers\botWorker;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sys\Ts3LogController;
use App\Http\Controllers\ts3Config\Ts3UriStringHelperController;
use App\Models\ts3Bot\ts3BotLog;
use App\Models\ts3Bot\ts3Channel;
use App\Models\ts3Bot\ts3ServerConfig;
use App\Models\ts3BotWorkers\ts3BotWorkerChannelsCreate;
use App\Models\ts3BotWorkers\ts3BotWorkerChannelsRemove;
use Exception;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\Adapter;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TeamSpeak3Exception;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Node;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

class ClearingWorkerController extends Controller
{
    protected int $server_id;

    protected string $qaName;

    protected Server|Adapter|Host|Node $ts3_VirtualServer;

    protected Ts3LogController $logController;

    public function __construct(int $server_id)
    {
        //declare
        $this->server_id = $server_id;
        $this->logController = new Ts3LogController('Clearing-Worker', $this->server_id);
    }

    /**
     * @throws Exception
     */
    public function startClearing(): void
    {
        //get Server config
        $ts3ServerConfig = ts3ServerConfig::query()
            ->where('id', '=', $this->server_id)->first();

        if ($ts3ServerConfig->qa_nickname != null) {
            $this->qaName = $ts3ServerConfig->qa_nickname;
        } else {
            $this->qaName = $ts3ServerConfig->qa_name;
        }

        //get uri with StringHelper
        $Ts3UriStringHelper = new Ts3UriStringHelperController();
        $uri = $Ts3UriStringHelper->getStandardUriString(
            $ts3ServerConfig->qa_name,
            $ts3ServerConfig->qa_pw,
            $ts3ServerConfig->server_ip,
            $ts3ServerConfig->server_query_port,
            $ts3ServerConfig->server_port,
            $this->qaName.'-Clearing-Worker',
            $this->server_id,
            $ts3ServerConfig->mode
        );

        try {
            //connect to above specified server
            $this->ts3_VirtualServer = TeamSpeak3::factory($uri);
        } catch(TeamSpeak3Exception $e) {
            //set log
            $this->logController->setLog($e, ts3BotLog::FAILED, 'Start Clearing-Worker');
            //disconnect from server
            $this->ts3_VirtualServer->getParent()->getTransport()->disconnect();
        }

        //update db from ts3 server
        $this->updateChannelList();
    }

    private function updateChannelList(): void
    {
        try {
            //get all channels as collection without SubChannels
            $updateTsChannels = collect($this->ts3_VirtualServer->channelList());

            //get array from existing channels
            $channelList = [];

            foreach ($updateTsChannels->keys()->all() as $cid) {
                $channelList[] = $cid;
            }

            //get for each key - channelID connection the channel info and store in db
            foreach ($updateTsChannels->keys()->all() as $cid) {
                //get channel by id
                $channel = $this->ts3_VirtualServer->channelGetById($cid);
                //get channel info
                $channelInfo = $channel->getInfo();
                //update or create channel information
                $this->updateChannelInDatabase($cid, $channelInfo, $channel->toString());
            }

            //get channels where not found at server side and delete in database
            $deletingChannelList = ts3Channel::query()
                ->where('server_id', '=', $this->server_id)
                ->whereNotIn('cid', $channelList)
                ->get();

            //update channel information
            foreach ($deletingChannelList as $deleteChannelsFromDB) {
                $this->deleteChannelFromDB($deleteChannelsFromDB->cid);
            }
        } catch(TeamSpeak3Exception | Exception $e) {
            //set log
            $this->logController->setLog($e, ts3BotLog::FAILED, 'Update Channel List');
            //disconnect from server
            $this->ts3_VirtualServer->getAdapter()->getTransport()->disconnect();
        }
    }

    private function updateChannelInDatabase(int $cid, array $channelInfo, string $channelName): void
    {
        //store channel information in bot brain db
        ts3Channel::query()
            ->where('server_id', '=', $this->server_id)
            ->where('cid', '=', $cid)
            ->updateOrCreate(
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
        ts3Channel::query()
            ->where('server_id', '=', $this->server_id)
            ->where('cid', '=', $cid)
            ->delete();

        ts3BotWorkerChannelsCreate::query()
            ->where('server_id', '=', $this->server_id)
            ->where('on_cid', '=', $cid)
            ->delete();

        ts3BotWorkerChannelsRemove::query()
            ->where('server_id', '=', $this->server_id)
            ->where('channel_cid', '=', $cid)
            ->delete();
    }
}
