<?php

namespace App\Http\Controllers\sys;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ts3Config\Ts3UriStringHelperController;
use App\Http\Requests\Migration\StartMigrationRequest;
use App\Models\ts3Bot\ts3ServerConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\Adapter;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\AdapterException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\NodeException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TransportException;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Node;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

class MigrationController extends Controller
{
    protected Server|Adapter|Node|Host $sourceConnection;
    protected Server|Adapter|Node|Host $targetConnection;
    protected int $source_server_id = 0;
    protected int $target_server_id = 0;
    public function viewMigration()
    {
        $servers = ts3ServerConfig::query()
            ->where('user_id','=', Auth::user()->id)
            ->get();

        return view('backend.utils.migration.migrate')->with([
            'servers'=>$servers,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function startMigration(StartMigrationRequest $request)
    {
        ini_set('max_execution_time', 18000);

        $this->source_server_id = $request->validated('source_server_id');
        $this->target_server_id = $request->validated('target_server_id');

        $this->setup_connections();

        //get logs
        $today = now()->format('Y-m-d');;
        $logPath = storage_path('logs/migration-'.$today.'.log');
        $log = file_get_contents($logPath);

        return redirect()->back()->with(['logs'=>$log]);
    }

    /**
     * @throws \Exception
     */
    private function setup_connections()
    {
        $source_server_config = ts3ServerConfig::query()
            ->where('id','=',$this->source_server_id)
            ->first();

        //setup source server
        $uriSourceHelperClass = new Ts3UriStringHelperController();
        $uriSource = $uriSourceHelperClass->getStandardUriString(
            $source_server_config->qa_name,
            $source_server_config->qa_pw,
            $source_server_config->server_ip,
            $source_server_config->server_query_port,
            $source_server_config->server_port,
            'migrate-bot',
            $this->source_server_id
        );

        $target_server_config = ts3ServerConfig::query()
            ->where('id','=',$this->target_server_id)
            ->first();
        //setup source server
        $uriTargetHelperClass = new Ts3UriStringHelperController();
        $uriTarget = $uriTargetHelperClass->getStandardUriString(
            $target_server_config->qa_name,
            $target_server_config->qa_pw,
            $target_server_config->server_ip,
            $target_server_config->server_query_port,
            $target_server_config->server_port,
            'migrate-bot',
            $this->target_server_id
        );

        try {
            $this->sourceConnection = TeamSpeak3::factory($uriSource);
        }catch(\Exception $e) {
            Log::channel('migration')->error('Connect to Source Server failed: '.$e->getMessage());
        }

        try {
            $this->targetConnection = TeamSpeak3::factory($uriTarget);
        }catch(\Exception $e) {
            Log::channel('migration')->error('Connect to Target Server failed: '.$e->getMessage());
        }

        Log::channel('migration')->info('## Start Migration ##');
        //migration channels with permissions
//        $this->migrate_channels();
//        Log::channel('migration')->error('Create Channel Completed');

        //migrate servergroups with permissions
        $this->migrate_servergroups();
        Log::channel('migration')->info('Create Servergroups Completed');

        Log::channel('migration')->info('## Migration Completed ##');

    }

    /**
     * @throws AdapterException
     * @throws ServerQueryException
     * @throws TransportException
     */
    private function migrate_channels()
    {
        $sourceChannelList = $this->sourceConnection->channelList();
        $pid = 0;

        foreach ($sourceChannelList as $sourceChannel) {
            try {
                $sourceChannelInfo = $sourceChannel->getInfo();
                if ($sourceChannelInfo['pid'] == 0) {
                    $pid = $this->targetConnection->channelCreate([
                        'channel_name' => $sourceChannelInfo['channel_name'],
                        'channel_topic' => $sourceChannelInfo['channel_topic'],
                        'channel_flag_default' => $sourceChannelInfo['channel_flag_default'],
                        'channel_flag_password' => $sourceChannelInfo['channel_flag_password'],
                        'channel_flag_permanent' => $sourceChannelInfo['channel_flag_permanent'],
                        'channel_flag_semi_permanent' => $sourceChannelInfo['channel_flag_semi_permanent'],
                        'channel_codec' => $sourceChannelInfo['channel_codec'],
                        'channel_codec_quality' => $sourceChannelInfo['channel_codec_quality'],
                        'channel_needed_talk_power' => $sourceChannelInfo['channel_needed_talk_power'],
                        'total_clients_family' => $sourceChannelInfo['total_clients_family'],
                        'channel_maxclients' => $sourceChannelInfo['channel_maxclients'],
                        'channel_maxfamilyclients' => $sourceChannelInfo['channel_maxfamilyclients'],
                        'total_clients' => $sourceChannelInfo['total_clients'],
                        'channel_needed_subscribe_power' => $sourceChannelInfo['channel_needed_subscribe_power'],
                        'channelinfo' => $sourceChannelInfo['channelinfo'],
                        'channel_description' => $sourceChannelInfo['channel_description'],
                        'channel_password' => $sourceChannelInfo['channel_password'],
                        'channel_codec_latency_factor' => $sourceChannelInfo['channel_codec_latency_factor'],
                        'channel_codec_is_unencrypted' => $sourceChannelInfo['channel_codec_is_unencrypted'],
                        'channel_flag_maxclients_unlimited' => $sourceChannelInfo['channel_flag_maxclients_unlimited'],
                        'channel_flag_maxfamilyclients_unlimited' => $sourceChannelInfo['channel_flag_maxfamilyclients_unlimited'],
                        'channel_flag_maxfamilyclients_inherited' => $sourceChannelInfo['channel_flag_maxfamilyclients_inherited'],
                        'channel_forced_silence' => $sourceChannelInfo['channel_forced_silence'],
                        'channel_name_phonetic' => $sourceChannelInfo['channel_name_phonetic'],
                        'channel_banner_gfx_url' => $sourceChannelInfo['channel_banner_gfx_url'],
                        'channel_banner_mode' => $sourceChannelInfo['channel_banner_mode'],
                    ]);
                } else {
                    $pid = $this->targetConnection->channelCreate([
                        'cpid' => $pid,
                        'channel_name' => $sourceChannelInfo['channel_name'],
                        'channel_topic' => $sourceChannelInfo['channel_topic'],
                        'channel_flag_default' => $sourceChannelInfo['channel_flag_default'],
                        'channel_flag_password' => $sourceChannelInfo['channel_flag_password'],
                        'channel_flag_permanent' => $sourceChannelInfo['channel_flag_permanent'],
                        'channel_flag_semi_permanent' => $sourceChannelInfo['channel_flag_semi_permanent'],
                        'channel_codec' => $sourceChannelInfo['channel_codec'],
                        'channel_codec_quality' => $sourceChannelInfo['channel_codec_quality'],
                        'channel_needed_talk_power' => $sourceChannelInfo['channel_needed_talk_power'],
                        'total_clients_family' => $sourceChannelInfo['total_clients_family'],
                        'channel_maxclients' => $sourceChannelInfo['channel_maxclients'],
                        'channel_maxfamilyclients' => $sourceChannelInfo['channel_maxfamilyclients'],
                        'total_clients' => $sourceChannelInfo['total_clients'],
                        'channel_needed_subscribe_power' => $sourceChannelInfo['channel_needed_subscribe_power'],
                        'channelinfo' => $sourceChannelInfo['channelinfo'],
                        'channel_description' => $sourceChannelInfo['channel_description'],
                        'channel_password' => $sourceChannelInfo['channel_password'],
                        'channel_codec_latency_factor' => $sourceChannelInfo['channel_codec_latency_factor'],
                        'channel_codec_is_unencrypted' => $sourceChannelInfo['channel_codec_is_unencrypted'],
                        'channel_flag_maxclients_unlimited' => $sourceChannelInfo['channel_flag_maxclients_unlimited'],
                        'channel_flag_maxfamilyclients_unlimited' => $sourceChannelInfo['channel_flag_maxfamilyclients_unlimited'],
                        'channel_flag_maxfamilyclients_inherited' => $sourceChannelInfo['channel_flag_maxfamilyclients_inherited'],
                        'channel_forced_silence' => $sourceChannelInfo['channel_forced_silence'],
                        'channel_name_phonetic' => $sourceChannelInfo['channel_name_phonetic'],
                        'channel_banner_gfx_url' => $sourceChannelInfo['channel_banner_gfx_url'],
                        'channel_banner_mode' => $sourceChannelInfo['channel_banner_mode'],
                    ]);
                }

                //set permissions
                $sourceChannelPermissions = $this->sourceConnection->channelPermList($sourceChannelInfo['cid'], true);

                foreach ($sourceChannelPermissions as $sourceChannelPermission) {
                    $this->targetConnection->channelPermAssign($pid, [$sourceChannelPermission['permsid']],
                        $sourceChannelPermission['permvalue']);
                }
            }catch (\Exception $e) {
                Log::channel('migration')->error('Create Servergroup failed: '.$e->getMessage());
            }
        }
    }

    /**
     * @throws AdapterException
     * @throws NodeException
     * @throws TransportException
     * @throws ServerQueryException
     */
    private function migrate_servergroups()
    {
        $sourceServerGroupList = $this->sourceConnection->serverGroupList(['type'=>1]);

        foreach ($sourceServerGroupList as $sourceServerGroup) {
            //create servergroup
            $sourceServerGroupInfo = $sourceServerGroup->getInfo();
            try {
                $sid = $this->targetConnection->serverGroupCreate($sourceServerGroupInfo['name'],1);

                //add permissions
                $sourceServerGroupPermissions = $this->sourceConnection->serverGroupPermList($sourceServerGroupInfo['sgid'], true);

                foreach ($sourceServerGroupPermissions as $sourceServerGroupPermission) {
                    try {
                        $this->targetConnection->serverGroupPermAssign($sid, [$sourceServerGroupPermission['permsid']],[$sourceServerGroupPermission['permvalue']],[$sourceServerGroupPermission['permnegated']],[$sourceServerGroupPermission['permskip']]);
                    }catch (\Exception $e) {
                        Log::channel('migration')->error('Create permsid: '.$sourceServerGroupPermission['permsid'].' failed. | '.$e->getMessage());
                    }
                }
            }catch (\Exception $e) {
                Log::channel('migration')->error('Create '.$sourceServerGroupInfo['name'].' failed: '.$e->getMessage());
            }
        }
    }
    private function migrate_channelgroups()
    {

    }
    private function migrate_ban_list()
    {

    }
}
