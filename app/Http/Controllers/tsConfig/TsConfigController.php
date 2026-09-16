<?php

namespace App\Http\Controllers\tsConfig;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sys\StatisticController;
use App\Http\Controllers\sys\TsLogController;
use App\Http\Requests\TsConfig\CreateStartBotRequest;
use App\Http\Requests\TsConfig\CreateStopBotRequest;
use App\Models\tsBot\tsBotLog;
use App\Models\tsBot\tsChannel;
use App\Models\tsBot\tsChannelGroup;
use App\Models\tsBot\tsServerConfig;
use App\Models\tsBot\tsServerGroup;
use Exception;
use Illuminate\Support\Facades\Auth;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

class TsConfigController extends Controller
{
    protected TsLogController $tsLogController;

    protected StatisticController $statisticController;

    protected null|string $uri = null;

    public function tsServerCheckConfig(int $server_id)
    {
    }

    /**
     * @throws Exception
     */
    public function tsServerInitializing(int $server_id): array
    {
        $this->tsLogController = new TsLogController('Server initializing', Auth::user()->active_server_id);

        $tsServerConfig = tsServerConfig::query()
            ->where('id', '=', $server_id)
            ->first();

        try {
            $uri = new TsUriStringHelperController();
            $this->uri = $uri->getStandardUriString(
                $tsServerConfig->qa_name,
                $tsServerConfig->qa_pw,
                $tsServerConfig->server_ip,
                $tsServerConfig->server_query_port,
                $tsServerConfig->server_port,
                $tsServerConfig->qa_name,
                $server_id,
            );
        } catch (Exception) {
            redirect()->back()->withErrors(['ipAddress'=>'The ip address or dns name you entered is invalid.']);
        }

        try {
            TeamSpeak3::init();
            $tsVirtualServer = TeamSpeak3::factory($this->uri);
            $this->statisticController = new StatisticController();
        } catch (Exception $e) {
            $this->tsLogController->setCustomLog(
                $server_id,
                tsBotLog::FAILED,
                'Connect to server failed',
                'Setup - Initializing Server',
                $e->getCode(),
                $e->getMessage()
            );

            // print the error message returned by the server
            return ['status'=>0, 'msg'=>'Fehler: '.$e->getCode().': '.$e->getMessage()];
        }

        try {
            //CHANNELS
            //get all channels as a collection without SubChannels
            $tsChannels = collect($tsVirtualServer->channelList(['pid'=>0]));
            //get for each key - channelID connection the channel info and store in db
            foreach ($tsChannels->keys()->all() as $cid) {
                //get channel by id
                $channel = $tsVirtualServer->channelGetById($cid);
                //get channel info
                $channelInfo = $channel->getInfo();
                //store info
                $this->createChannels($server_id, $channelInfo, $channel->toString());

                //sub-channels available
                $subChannels = collect($channel->subChannelList());
                foreach ($subChannels->keys()->all() as $subChannelCid) {
                    $subChannel = $tsVirtualServer->channelGetById($subChannelCid);
                    $subChannelInfo = $subChannel->getInfo();
                    $this->createChannels($server_id, $subChannelInfo, $subChannel->toString());
                }
            }
        } catch (Exception $e) {
            $this->tsLogController->setCustomLog(
                $server_id,
                tsBotLog::FAILED,
                'Setup - Channels',
                '',
                $e->getMessage(),
                $e->getCode(),
            );

            // print the error message returned by the server
            return ['status'=>0, 'msg'=>'Fehler: '.$e->getCode().': '.$e->getMessage()];
        }

        try {
            //SERVER-GROUPS
            //get server groups as a collection
            $tsServerGroups = collect($tsVirtualServer->serverGroupList());
            //insert server groups in db
            foreach ($tsServerGroups->keys()->all() as $sgid) {
                //get server group by id
                $serverGroup = $tsVirtualServer->serverGroupGetById($sgid);
                $serverGroupInfo = $serverGroup->getInfo();
                //store info
                $this->createServerGroups($server_id, $serverGroupInfo);
            }
        } catch (Exception $e) {
            $this->tsLogController->setCustomLog(
                $server_id,
                tsBotLog::FAILED,
                'Setup - Server Groups',
                '',
                $e->getCode(),
                $e->getMessage(),
            );

            // print the error message returned by the server
            return ['status'=>0, 'msg'=>'Fehler: '.$e->getCode().': '.$e->getMessage()];
        }

        try {
            //CHANNEL GROUPS
            //get channel Groups
            $tsChannelGroups = collect($tsVirtualServer->channelGroupList());
            //insert channel groups in db
            foreach ($tsChannelGroups->keys()->all() as $cgid) {
                //get channel group by id
                $channelGroup = $tsVirtualServer->channelGroupGetById($cgid);
                $channelGroupInfo = $channelGroup->getInfo();
                //store info
                $this->createChannelGroups($server_id, $channelGroupInfo);
            }
        } catch (Exception $e) {
            $this->tsLogController->setCustomLog(
                $server_id,
                tsBotLog::FAILED,
                'Setup - Channel Groups',
                '',
                $e->getCode(),
                $e->getMessage(),
            );

            // print the error message returned by the server
            return ['status'=>0, 'msg'=>'Fehler: '.$e->getCode().': '.$e->getMessage()];
        }

        $this->tsLogController->setCustomLog(
            $server_id,
            tsBotLog::SUCCESS,
            'Config Initialization',
            'The server has been successfully initialized.',
        );

        //update virtual server statistic
        $this->statisticController->gatherVirtualServerStatistic($server_id, $tsVirtualServer);

        return ['status'=>1, 'msg'=>'success'];
    }

    public function tsStartBot(CreateStartBotRequest $request): \Illuminate\Http\RedirectResponse
    {
        $logController = new TsLogController('Webinterface', $request->validated('server_id'));
        $logController->setCustomLog(
            $request->validated('server_id'),
            tsBotLog::SUCCESS,
            'startBot',
            'Bot started via web interface',
        );

        tsServerConfig::query()
            ->where('id', '=', $request->validated('server_id'))
            ->update([
                'is_ts_start'=>true,
                'is_active'=>true,
            ]);

        return redirect()->back()->with('success', 'The bot is started and immediately logs onto the server.');
    }

    public function tsStopBot(CreateStopBotRequest $request): \Illuminate\Http\RedirectResponse
    {
        $logController = new TsLogController('Webinterface', $request->validated('server_id'));
        $logController->setCustomLog(
            $request->validated('server_id'),
            tsBotLog::SUCCESS,
            'botStop',
            'Bot shutting down via web interface',
        );

        tsServerConfig::query()
            ->where('id', '=', $request->validated('server_id'))
            ->update([
                'is_ts_start'=>false,
                'is_active'=>false,
            ]);

        return redirect()->back()->with('success', 'Bot is shutting down. This may take a moment.');
    }

    public function createChannels(int $server_id, array $channelInfo, string $channelName): void
    {
        tsChannel::query()->create([
            'server_id'=>$server_id,
            'cid'=>$channelInfo['cid'],
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
            'channel_banner_gfx_url'=>$channelInfo['channel_banner_gfx_url'] ?? 0,
            'channel_banner_mode'=>$channelInfo['channel_banner_mode'] ?? 0,
            'channel_description'=>$channelInfo['channel_description'] ?? null,
            'channel_password'=>$channelInfo['channel_password'] ?? 0,
            'channel_codec_latency_factor'=>$channelInfo['channel_codec_latency_factor'] ?? 0,
            'channel_codec_is_unencrypted'=>$channelInfo['channel_codec_is_unencrypted'] ?? 0,
            'channel_security_salt'=>$channelInfo['channel_security_salt'] ?? 0,
            'channel_delete_delay'=>$channelInfo['channel_delete_delay'] ?? 0,
            'channel_unique_identifier'=>$channelInfo['channel_unique_identifier'] ?? 0,
            'channel_flag_maxclients_unlimited'=>$channelInfo['channel_flag_maxclients_unlimited'] ?? 0,
            'channel_flag_maxfamilyclients_unlimited'=>$channelInfo['channel_flag_maxfamilyclients_unlimited'] ?? 0,
            'channel_flag_maxfamilyclients_inherited'=>$channelInfo['channel_flag_maxfamilyclients_inherited'] ?? 0,
            'channel_filepath'=>$channelInfo['channel_filepath'] ?? 0,
            'channel_forced_silence'=>$channelInfo['channel_forced_silence'] ?? 0,
            'channel_name_phonetic'=>$channelInfo['channel_name_phonetic'] ?? 0,
            'seconds_empty'=>$channelInfo['seconds_empty'] ?? 0,
        ]);
    }

    private function createServerGroups(int $server_id, array $serverGroupInfo): void
    {
        tsServerGroup::query()->create([
            'server_id'=>$server_id,
            'sgid'=>$serverGroupInfo['sgid'],
            'name'=>$serverGroupInfo['name'],
            'type'=>$serverGroupInfo['type'],
            'iconid'=>$serverGroupInfo['iconid'],
            'savedb'=>$serverGroupInfo['savedb'],
            'sortid'=>$serverGroupInfo['sortid'],
            'namemode'=>$serverGroupInfo['namemode'],
            'n_modifyp'=>$serverGroupInfo['n_modifyp'],
            'n_member_addp'=>$serverGroupInfo['n_member_addp'],
            'n_member_removep'=>$serverGroupInfo['n_member_removep'],
        ]);
    }

    private function createChannelGroups(int $server_id, array $channelGroupInfo): void
    {
        tsChannelGroup::query()->create([
            'server_id'=>$server_id,
            'cgid'=>$channelGroupInfo['cgid'],
            'name'=>$channelGroupInfo['name'],
            'type'=>$channelGroupInfo['type'],
            'iconid'=>$channelGroupInfo['iconid'],
            'savedb'=>$channelGroupInfo['savedb'],
            'sortid'=>$channelGroupInfo['sortid'],
            'namemode'=>$channelGroupInfo['namemode'],
            'n_modifyp'=>$channelGroupInfo['n_modifyp'],
            'n_member_addp'=>$channelGroupInfo['n_member_addp'],
            'n_member_removep'=>$channelGroupInfo['n_member_removep'],
        ]);
    }

    public function updateChannels(int $server_id, array $channelInfo, string $channelName, int $cid): void
    {
        tsChannel::query()
            ->where('server_id', '=', $server_id)
            ->where('cid', '=', $cid)
            ->update([
                'server_id'=>$server_id,
                'cid'=>$channelInfo['cid'],
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
            ]);
    }
}
