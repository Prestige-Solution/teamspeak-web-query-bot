<?php

namespace App\Http\Controllers\ts3Config;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sys\Ts3LogController;
use App\Http\Requests\Ts3Config\CreateStartBotRequest;
use App\Http\Requests\Ts3Config\CreateStopBotRequest;
use App\Jobs\ts3BotStartQueue;
use App\Models\ts3Bot\ts3BotLog;
use App\Models\ts3Bot\ts3Channel;
use App\Models\ts3Bot\ts3ChannelGroup;
use App\Models\ts3Bot\ts3ServerConfig;
use App\Models\ts3Bot\ts3ServerGroup;
use App\Models\ts3Bot\ts3UserDatabase;
use App\Models\ts3BotWorkers\ts3BotWorkerAfk;
use App\Models\ts3BotWorkers\ts3BotWorkerChannelsCreate;
use App\Models\ts3BotWorkers\ts3BotWorkerChannelsRemove;
use App\Models\ts3BotWorkers\ts3BotWorkerPolice;
use Exception;
use Illuminate\Support\Facades\Auth;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TeamSpeak3Exception;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

class Ts3ConfigController extends Controller
{
    protected Ts3LogController $ts3LogController;

    protected null|string $uri = null;

    public function ts3ServerCheckConfig(int $serverID)
    {
    }

    /**
     * @throws Exception
     */
    public function ts3ServerInitializing(int $server_id): array
    {
        $this->ts3LogController = new Ts3LogController('Server Initialising', Auth::user()->default_server_id);

        $ts3ServerConfig = ts3ServerConfig::query()
            ->where('id', '=', $server_id)
            ->first();

        //clear tables
        ts3Channel::query()->where('server_id', '=', $server_id)->delete();
        ts3ServerGroup::query()->where('server_id', '=', $server_id)->delete();
        ts3ChannelGroup::query()->where('server_id', '=', $server_id)->delete();
        ts3UserDatabase::query()->where('server_id', '=', $server_id)->delete();
        ts3BotWorkerChannelsCreate::query()->where('server_id', '=', $server_id)->delete();
        ts3BotWorkerAfk::query()->where('server_id', '=', $server_id)->delete();
        ts3BotWorkerChannelsRemove::query()->where('server_id', '=', $server_id)->delete();
        ts3BotWorkerPolice::query()->where('server_id', '=', $server_id)->update(['allow_sgid_vpn'=>1]);

        //TODO Delete Banner configs and data

        try {
            $uri = new Ts3UriStringHelperController();
            $this->uri = $uri->getStandardUriString(
                $ts3ServerConfig->qa_name,
                $ts3ServerConfig->qa_pw,
                $ts3ServerConfig->server_ip,
                $ts3ServerConfig->server_query_port,
                $ts3ServerConfig->server_port,
                $ts3ServerConfig->qa_name,
                $server_id,
                $ts3ServerConfig->mode
            );
        } catch (TeamSpeak3Exception) {
            redirect()->back()->withErrors(['ipAddress'=>'The ip address or dns name you entered is invalid.']);
        }

        try {
            TeamSpeak3::init();
            $ts3_VirtualServer = TeamSpeak3::factory($this->uri);
        } catch (TeamSpeak3Exception $e) {
            $this->ts3LogController->setLog(
                $e,
                ts3BotLog::FAILED,
                'Setup - Initialising Server',
            );

            // print the error message returned by the server
            return ['status'=>0, 'msg'=>'Fehler: '.$e->getCode().': '.$e->getMessage()];
        }

        try {
            //CHANNELS
            //get all channels as collection without SubChannels
            $ts3Channels = collect($ts3_VirtualServer->channelList(['pid'=>0]));
            //get for each key - channelID connection the channel info and store in db
            foreach ($ts3Channels->keys()->all() as $cid) {
                //get channel by id
                $channel = $ts3_VirtualServer->channelGetById($cid);
                //get channel info
                $channelInfo = $channel->getInfo();
                //store info
                $this->createChannels($server_id, $channelInfo, $channel->toString());

                //sub-channels available
                $subChannels = collect($channel->subChannelList());
                foreach ($subChannels->keys()->all() as $subChannelCid) {
                    $subChannel = $ts3_VirtualServer->channelGetById($subChannelCid);
                    $subChannelInfo = $subChannel->getInfo();
                    $this->createChannels($server_id, $subChannelInfo, $subChannel->toString());
                }
            }
        } catch (TeamSpeak3Exception $e) {
            $this->ts3LogController->setLog(
                $e,
                ts3BotLog::FAILED,
                'Setup - Channels',
            );

            // print the error message returned by the server
            return ['status'=>0, 'msg'=>'Fehler: '.$e->getCode().': '.$e->getMessage()];
        }

        try {
            //SERVER-GROUPS
            //get server groups as collection
            $ts3ServerGroups = collect($ts3_VirtualServer->serverGroupList());
            //insert server groups in db
            foreach ($ts3ServerGroups->keys()->all() as $sgid) {
                //get server group by id
                $serverGroup = $ts3_VirtualServer->serverGroupGetById($sgid);
                $serverGroupInfo = $serverGroup->getInfo();
                //store info
                $this->createServerGroups($server_id, $serverGroupInfo);
            }
        } catch (TeamSpeak3Exception $e) {
            $this->ts3LogController->setLog(
                $e,
                ts3BotLog::FAILED,
                'Setup - Server Groups',
            );

            // print the error message returned by the server
            return ['status'=>0, 'msg'=>'Fehler: '.$e->getCode().': '.$e->getMessage()];
        }

        try {
            //CHANNEL GROUPS
            //get channel Groups
            $ts3ChannelGroups = collect($ts3_VirtualServer->channelGroupList());
            //insert channel groups in db
            foreach ($ts3ChannelGroups->keys()->all() as $cgid) {
                //get channel group by id
                $channelGroup = $ts3_VirtualServer->channelGroupGetById($cgid);
                $channelGroupInfo = $channelGroup->getInfo();
                //store info
                $this->createChannelGroups($server_id, $channelGroupInfo);
            }
        } catch (TeamSpeak3Exception $e) {
            $this->ts3LogController->setLog(
                $e,
                ts3BotLog::FAILED,
                'Setup - Channel Groups',
            );

            // print the error message returned by the server
            return ['status'=>0, 'msg'=>'Fehler: '.$e->getCode().': '.$e->getMessage()];
        }

        try {
            //TS3 DATABASE
            $usersTs3DB = collect($ts3_VirtualServer->clientListDb());

            //insert users
            foreach ($usersTs3DB->keys()->all() as $cldbid) {
                //get userinfo by db id
                $userDbInfo = $ts3_VirtualServer->clientInfoDb($cldbid);
                //store info
                $this->createUserDatabase($server_id, $userDbInfo);
            }
        } catch (TeamSpeak3Exception $e) {
            $this->ts3LogController->setLog(
                $e,
                ts3BotLog::FAILED,
                'Setup - TS3 Database',
            );

            // print the error message returned by the server
            return ['status'=>0, 'msg'=>'Fehler: '.$e->getCode().': '.$e->getMessage()];
        }

        $this->ts3LogController->setCustomLog(
            $server_id,
            ts3BotLog::SUCCESS,
            'Config Initialisation',
            'Der Server wurde erfolgreich initialisiert',
        );

        return ['status'=>1, 'msg'=>'success'];
    }

    public function ts3StartBot(CreateStartBotRequest $request): \Illuminate\Http\RedirectResponse
    {
        $logController = new Ts3LogController('Webinterface', $request->validated('server_id'));
        $logController->setCustomLog(
            $request->validated('server_id'),
            ts3BotLog::RUNNING,
            'startBot',
            'Bot was started via web interface',
        );

        ts3ServerConfig::query()
            ->where('id', '=', $request->validated('server_id'))
            ->update([
                'is_ts3_start'=>1,
                'is_active'=>1,
            ]);

        ts3BotStartQueue::dispatch($request->validated('server_id'))->onConnection('bot')->onQueue('bot');

        return redirect()->back()->with('success', 'The bot is started and immediately logs onto the server.');
    }

    public function ts3StopBot(CreateStopBotRequest $request): \Illuminate\Http\RedirectResponse
    {
        $logController = new Ts3LogController('Webinterface', $request->validated('server_id'));
        $logController->setCustomLog(
            $request->validated('server_id'),
            ts3BotLog::RUNNING,
            'botStop',
            'Bot was stopped via web interface',
        );

        ts3ServerConfig::query()
            ->where('id', '=', $request->validated('server_id'))
            ->update([
                'is_ts3_start'=>0,
                'is_active'=>0,
            ]);

        return redirect()->back()->with('success', 'Bot is stopped. This may take a moment.');
    }

    public function createChannels(int $server_id, array $channelInfo, string $channelName): void
    {
        ts3Channel::query()->create([
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
        ts3ServerGroup::query()->create([
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
        ts3ChannelGroup::query()->create([
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

    private function createUserDatabase(int $server_id, array $userDbInfo): void
    {
        ts3UserDatabase::query()->create([
            'server_id'=>$server_id,
            'client_unique_identifier'=>$userDbInfo['client_unique_identifier'],
            'client_nickname'=>$userDbInfo['client_nickname'],
            'client_database_id'=>$userDbInfo['client_database_id'],
            'client_created'=>$userDbInfo['client_created'],
            'client_lastconnected'=>$userDbInfo['client_lastconnected'],
            'client_totalconnections'=>$userDbInfo['client_totalconnections'],
            'client_flag_avatar'=>$userDbInfo['client_flag_avatar'],
            'client_description'=>$userDbInfo['client_description'],
            'client_month_bytes_uploaded'=>$userDbInfo['client_month_bytes_uploaded'],
            'client_month_bytes_downloaded'=>$userDbInfo['client_month_bytes_downloaded'],
            'client_total_bytes_uploaded'=>$userDbInfo['client_total_bytes_uploaded'],
            'client_total_bytes_downloaded'=>$userDbInfo['client_total_bytes_downloaded'],
            'client_base64HashClientUID'=>$userDbInfo['client_base64HashClientUID'],
            'client_lastip'=>$userDbInfo['client_lastip'],
        ]);
    }

    public function updateChannels(int $server_id, array $channelInfo, string $channelName, int $cid): void
    {
        ts3Channel::query()
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

    public function deleteForgetClients()
    {
        //TODO forget clients from database
    }
}
