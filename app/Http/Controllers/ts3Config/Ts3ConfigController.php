<?php

namespace App\Http\Controllers\ts3Config;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sys\Ts3LogController;
use App\Http\Requests\Ts3Config\CreateStartBotRequest;
use App\Http\Requests\Ts3Config\CreateStopBotRequest;
use App\Models\ts3Bot\ts3Channel;
use App\Models\ts3Bot\ts3ChannelGroup;
use App\Models\ts3Bot\ts3ServerConfig;
use App\Models\ts3Bot\ts3ServerGroup;
use App\Models\ts3Bot\ts3UserDatabase;
use App\Models\ts3BotJobs\ts3BotJobCreateChannels;
use App\Models\ts3BotWorkers\ts3BotWorkerAfk;
use App\Models\ts3BotWorkers\ts3BotWorkerChannelRemover;
use App\Models\ts3BotWorkers\ts3BotWorkerPolice;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TeamSpeak3Exception;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

class Ts3ConfigController extends Controller
{
    protected Ts3LogController $ts3LogController;

    public function ts3ServerInitializing($serverID): array
    {
        $this->ts3LogController = new Ts3LogController('Server Initialising', Auth::user()->server_id);

        $ts3ServerConfig = ts3ServerConfig::query()
            ->where('id','=',$serverID)
            ->first();
        $serverID = $ts3ServerConfig->id;

        //clear databases for re-init routine
        ts3Channel::query()->where('server_id','=',$serverID)->delete();
        ts3ServerGroup::query()->where('server_id','=',$serverID)->delete();
        ts3ChannelGroup::query()->where('server_id','=',$serverID)->delete();
        ts3UserDatabase::query()->where('server_id','=',$serverID)->delete();
        ts3BotJobCreateChannels::query()->where('server_id','=',$serverID)->delete();
        ts3BotWorkerAfk::query()->where('server_id','=',$serverID)->delete();
        ts3BotWorkerChannelRemover::query()->where('server_id','=',$serverID)->delete();
        ts3BotWorkerPolice::query()->where('server_id','=',$serverID)->update(['allow_sgid_vpn'=>1]);

        //TODO Delete Banner configs and data

        $uri = new Ts3UriStringHelperController();
        $uri = $uri->getStandardUriString(
            $ts3ServerConfig->qa_name,
            $ts3ServerConfig->qa_pw,
            $ts3ServerConfig->server_ip,
            $ts3ServerConfig->server_query_port,
            $ts3ServerConfig->server_port,
            $ts3ServerConfig->qa_name,
            $ts3ServerConfig->mode,
        );

        if ($uri == 0)
        {
            redirect()->back()->withErrors(['ipAddress'=>'Die Eingegeben IP Adresse ist nicht gültig']);
        }

        try {
            // Create new object of TS3 PHP Framework class
            $ts3_VirtualServer = TeamSpeak3::factory($uri);

        }catch (TeamSpeak3Exception|\Exception $e)
        {
            $this->ts3LogController->setLog(
                $e,
                4,
                'Setup - Initialising Server',
            );

            // print the error message returned by the server
            return ['status'=>0,'msg'=>'Fehler: ' . $e->getCode() . ': ' . $e->getMessage()];
        }

        try {
            //CHANNELS
            //get all channels as collection without SubChannels
            $ts3Channels = collect($ts3_VirtualServer->channelList(['pid'=>0]));
            //get for each key - channelID connection the channel info and store in db
            foreach ($ts3Channels->keys()->all() as $cid)
            {
                //get channel by id
                $channel = $ts3_VirtualServer->channelGetById($cid);
                //get channel info
                $channelInfo = $channel->getInfo();
                //store info
                $this->createChannels($serverID,$channelInfo,$channel->toString());

                //sub-channels available
                $subChannels = collect($channel->subChannelList());
                foreach ($subChannels->keys()->all() as $subChannelCid)
                {
                    $subChannel = $ts3_VirtualServer->channelGetById($subChannelCid);
                    $subChannelInfo = $subChannel->getInfo();
                    $this->createChannels($serverID,$subChannelInfo,$subChannel->toString());
                }
            }
        }catch (TeamSpeak3Exception $e)
        {
            $this->ts3LogController->setLog(
                $e,
                4,
                'Setup - Channels',
            );

            // print the error message returned by the server
            return ['status'=>0,'msg'=>'Fehler: ' . $e->getCode() . ': ' . $e->getMessage()];
        }

        try {
            //SERVER-GROUPS
            //get server groups as collection
            $ts3ServerGroups = collect($ts3_VirtualServer->serverGroupList());
            //insert server groups in db
            foreach ($ts3ServerGroups->keys()->all() as $sgid)
            {
                //get server group by id
                $serverGroup = $ts3_VirtualServer->serverGroupGetById($sgid);
                $serverGroupInfo = $serverGroup->getInfo();
                //store info
                $this->createServerGroups($serverID,$serverGroupInfo);
            }
        }catch (TeamSpeak3Exception $e)
        {
            $this->ts3LogController->setLog(
                $e,
                4,
                'Setup - Server Groups',
            );

            // print the error message returned by the server
            return ['status'=>0,'msg'=>'Fehler: ' . $e->getCode() . ': ' . $e->getMessage()];
        }

        try {
            //CHANNEL GROUPS
            //get channel Groups
            $ts3ChannelGroups = collect($ts3_VirtualServer->channelGroupList());
            //insert channel groups in db
            foreach ($ts3ChannelGroups->keys()->all() as $cgid)
            {
                //get channel group by id
                $channelGroup = $ts3_VirtualServer->channelGroupGetById($cgid);
                $channelGroupInfo = $channelGroup->getInfo();
                //store info
                $this->createChannelGroups($serverID,$channelGroupInfo);

            }
        }catch (TeamSpeak3Exception $e)
        {
            $this->ts3LogController->setLog(
                $e,
                4,
                'Setup - Channel Groups',
            );

            // print the error message returned by the server
            return ['status'=>0,'msg'=>'Fehler: ' . $e->getCode() . ': ' . $e->getMessage()];
        }

        try {
            //TS3 DATABASE
            $usersTs3DB = collect($ts3_VirtualServer->clientListDb());

            //insert users
            foreach ($usersTs3DB->keys()->all() as $cldbid)
            {
                //get userinfo by db id
                $userDbInfo = $ts3_VirtualServer->clientInfoDb($cldbid);
                //store info
                $this->createUserDatabase($serverID,$userDbInfo);
            }
        }catch (TeamSpeak3Exception $e)
        {
            $this->ts3LogController->setLog(
                $e,
                4,
                'Setup - TS3 Database',
            );

            // print the error message returned by the server
            return ['status'=>0,'msg'=>'Fehler: ' . $e->getCode() . ': ' . $e->getMessage()];
        }

        $this->ts3LogController->setCustomLog(
            $serverID,
            '5',
            'Config Initialisation',
            'Der Server wurde erfolgreich initialisiert',
            NULL,
            NULL,
        );

        return ['status'=>1,'msg'=>'success'];
    }

    public function ts3StartBot(CreateStartBotRequest $request): \Illuminate\Http\RedirectResponse
    {
        //set log
        $logController = new Ts3LogController('Webinterface',$request->validated('ServerID'));
        $logController->setCustomLog(
            $request->validated('ServerID'),
            1,
            'startBot',
            'Bot wurde via Webinterface gestartet',
            null,
            null,
        );

        //set bot active status
        ts3ServerConfig::query()
            ->where('id','=',$request->validated('ServerID'))
            ->update([
                'ts3_start_stop'=>1,
                'active'=>1,
            ]);

        Artisan::call('app:start-bot '.$request->validated('ServerID'));

        return redirect()->back()->with('success', 'Der Bot wird gestartet und loggt sich gleich auf den Server ein.');
    }

    public function ts3StopBot(CreateStopBotRequest $request): \Illuminate\Http\RedirectResponse
    {
        //set log
        $logController = new Ts3LogController('Webinterface',$request->validated('ServerID'));
        $logController->setCustomLog(
            $request->validated('ServerID'),
            1,
            'botStop',
            'Bot durch das Webinterface gestoppt',
            null,
            null,
        );

        //set bot active status
        ts3ServerConfig::query()
            ->where('id','=',$request->validated('ServerID'))
            ->update([
                'ts3_start_stop'=>0,
                'active'=>0,
            ]);

        return redirect()->back()->with('success', 'Bot wird gestoppt. Dies kann einen Moment dauern.');
    }

    public function createChannels(int $serverID, array $channelInfo, string $channelName): void
    {
        //store channel information in bot brain db
        ts3Channel::query()->create([
            'server_id'=>$serverID,
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
            'channel_banner_gfx_url'=>$channelInfo['channel_banner_gfx_url'] ?? 0, //weg
            'channel_banner_mode'=>$channelInfo['channel_banner_mode'] ?? 0, // weg
            'channel_description'=>$channelInfo['channel_description'] ?? NULL,
            'channel_password'=>$channelInfo['channel_password'] ?? 0, // weg
            'channel_codec_latency_factor'=>$channelInfo['channel_codec_latency_factor'] ?? 0, // weg
            'channel_codec_is_unencrypted'=>$channelInfo['channel_codec_is_unencrypted'] ?? 0, // weg
            'channel_security_salt'=>$channelInfo['channel_security_salt'] ?? 0, //weg
            'channel_delete_delay'=>$channelInfo['channel_delete_delay'] ?? 0, //weg
            'channel_unique_identifier'=>$channelInfo['channel_unique_identifier'] ?? 0, //weg
            'channel_flag_maxclients_unlimited'=>$channelInfo['channel_flag_maxclients_unlimited'] ?? 0, //weg
            'channel_flag_maxfamilyclients_unlimited'=>$channelInfo['channel_flag_maxfamilyclients_unlimited'] ?? 0, //weg
            'channel_flag_maxfamilyclients_inherited'=>$channelInfo['channel_flag_maxfamilyclients_inherited'] ?? 0, //weg
            'channel_filepath'=>$channelInfo['channel_filepath'] ?? 0, //weg
            'channel_forced_silence'=>$channelInfo['channel_forced_silence'] ?? 0, //weg
            'channel_name_phonetic'=>$channelInfo['channel_name_phonetic'] ?? 0, //weg
            'seconds_empty'=>$channelInfo['seconds_empty'] ?? 0, //weg
        ]);
    }

    private function createServerGroups($serverID, $serverGroupInfo): void
    {
        ts3ServerGroup::query()->create([
            'server_id'=>$serverID,
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

    private function createChannelGroups($serverID, $channelGroupInfo): void
    {
        ts3ChannelGroup::query()->create([
            'server_id'=>$serverID,
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

    private function createUserDatabase($serverID, $userDbInfo): void
    {
        ts3UserDatabase::query()->create([
            'server_id'=>$serverID,
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

    public function updateChannels($serverID, $channelInfo,$channelName, $cid): void
    {
        //store channel information in bot brain db
        ts3Channel::query()
            ->where('server_id','=',$serverID)
            ->where('cid','=',$cid)
            ->update([
            'server_id'=>$serverID,
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
            'channel_description'=>$channelInfo['channel_description'] ?? NULL,
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
