<?php

namespace App\Http\Controllers\bot;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sys\StatisticController;
use App\Http\Controllers\sys\TsLogController;
use App\Http\Controllers\tsConfig\BadNameController;
use App\Http\Controllers\tsConfig\TsUriStringHelperController;
use App\Models\tsBot\tsBotLog;
use App\Models\tsBot\tsChannel;
use App\Models\tsBot\tsServerConfig;
use App\Models\tsBotWorkers\tsBotWorkerChannelsCreate;
use App\Models\tsBotWorkers\tsBotWorkerChannelsRemove;
use App\Models\tsBotWorkers\tsBotWorkerPolice;
use Exception;
use Illuminate\Support\Str;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\Adapter;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\AdapterException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\NodeException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TeamSpeak3Exception;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TransportException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Node;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

class TsBotController extends Controller
{
    protected Server|Adapter|Node|Host $tsVirtualServer;

    protected TsLogController $logController;

    protected StatisticController $statisticController;

    protected int $serverId;

    protected int $waitIncrease = 1;

    protected int $waitTimeSeconds = 10;

    protected int $selfClid;

    protected int $standardChannelId;

    protected int $reconnectCode;

    protected bool $isBotStop = false;

    /**
     * @throws Exception
     */
    public function __construct(int $serverId)
    {
        $this->serverId = $serverId;
        $this->reconnectCode = tsServerConfig::BotReconnectFalse; //Default is dont try to reconnect
        $this->logController = new TsLogController('Bot', $this->serverId);

        try {
            TeamSpeak3::init();
        } catch (TeamSpeak3Exception $e) {
            $this->logController->setLog(
                $e,
                tsBotLog::FAILED,
                'Pre-Check Bot Requirements'
            );
        }

        $this->startBot();
    }

    /**
     * @throws Exception
     */
    public function startBot(): void
    {
        try {
            $tsServerConfig = tsServerConfig::query()
                ->where('id', '=', $this->serverId)->first();

            if ($tsServerConfig === null) {
                $this->logController->setCustomLog(
                    $this->serverId,
                    tsBotLog::FAILED,
                    'startBot',
                    'Server configuration not found',
                );

                return;
            }

            if ($tsServerConfig->qa_nickname != null) {
                $qaName = $tsServerConfig->qa_nickname;
            } else {
                $qaName = $tsServerConfig->qa_name;
            }

            //get uri with StringHelper
            $tsStringHelper = new TsUriStringHelperController();
            $uri = $tsStringHelper->getStandardUriString(
                $tsServerConfig->qa_name,
                $tsServerConfig->qa_pw,
                $tsServerConfig->server_ip,
                $tsServerConfig->server_query_port,
                $tsServerConfig->server_port,
                $qaName,
                $this->serverId,
            );

            $this->tsVirtualServer = TeamSpeak3::factory($uri);
            $this->statisticController = new StatisticController();

            $whoami = $this->tsVirtualServer->whoami();
            $this->selfClid = $whoami['client_id'];
            $this->standardChannelId = $whoami['client_channel_id'];

            Signal::getInstance()->subscribe('serverqueryWaitTimeout', [$this, 'checkKeepAlive']);
            Signal::getInstance()->subscribe('notifyEvent', [$this, 'eventListener']);

            $this->tsVirtualServer->serverGetSelected()->notifyRegister('server');
            $this->tsVirtualServer->serverGetSelected()->notifyRegister('channel');

            tsServerConfig::query()->where('id', '=', $this->serverId)->update([
                'bot_status_id'=> tsBotLog::RUNNING,
            ]);

            $this->logController->setCustomLog(
                $this->serverId,
                tsBotLog::RUNNING,
                'startBot',
                'Bot started. Wait for events',
            );

            while ($this->isBotStop == false) {
                $this->tsVirtualServer->getParent()->getAdapter()->wait();
            }
        } catch(TeamSpeak3Exception $e) {
            $this->errorHandlingTeamSpeak3Exception($e);
        } catch (Exception $e) {
            $this->errorHandlingException($e->getCode(), $e->getMessage());
        } finally {
            if ($this->isBotStop == false) {
                //get bot active status
                $this->botStopSignal();

                //get reconnect code
                $this->reconnectCode = $this->reconnectBot();
            }

            //proof is a bot stop signal
            if ($this->isBotStop == true) {
                $this->logController->setCustomLog(
                    $this->serverId,
                    tsBotLog::SHUTDOWN,
                    'startBot',
                    'Bot shutting down',
                );
                if (isset($this->tsVirtualServer)) {
                    try {
                        $this->tsVirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
                    } catch (Exception) {
                    }
                }
            }

            //proof is a bot reconnect signal
            if ($this->reconnectCode == tsServerConfig::BotReconnectTrue) {
                $this->logController->setCustomLog(
                    $this->serverId,
                    tsBotLog::TRY_RECONNECT,
                    'startBot',
                    'Bot attempting to restart',
                );

                $this->startBot();
            } else {
                tsServerConfig::query()
                    ->where('id', '=', $this->serverId)
                    ->update([
                        'bot_status_id'=>tsBotLog::SHUTDOWN,
                        'is_ts_start'=>false,
                        'is_active'=>false,
                    ]);

                if (isset($this->tsVirtualServer)) {
                    try {
                        $this->tsVirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
                    } catch (Exception) {
                    }
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function checkKeepAlive(): void
    {
        if (config('app.bot_debug') == true) {
            echo "Check bot is alive \n";
        }

        //set stats
        $this->gatherVirtualServerStats();

        //check bot stop
        $this->botStopSignal();

        if ($this->isBotStop == false) {
            try {
                $keepAliveStatus = $this->tsVirtualServer->getAdapter()->request('clientupdate');

                if ($keepAliveStatus->getErrorProperty('msg')->toString() != 'ok') {
                    $this->logController->setCustomLog(
                        $this->serverId,
                        tsBotLog::FAILED,
                        'Check Keep Alive',
                        'Bot is dead! Restart Bot',
                    );

                    tsServerConfig::query()
                        ->where('id', '=', $this->serverId)
                        ->update([
                            'bot_status_id'=>tsBotLog::TRY_RECONNECT,
                        ]);
                }
            } catch (TeamSpeak3Exception $e) {
                $this->logController->setLog($e, tsBotLog::FAILED, 'Check Keep Alive');
            }
        }

        //reset wait increase to default = 1 if reconnect okay
        if ($this->waitIncrease > 1) {
            $this->waitIncrease = 1;
        }
    }

    /**
     * @throws Exception
     */
    public function eventListener($event): void
    {
        $this->botStopSignal();
        $getEvent = $event->getType()->toString();

        if (config('app.bot_debug') == true) {
            echo 'type: '.$getEvent."\n";
        }

        if ($getEvent == 'cliententerview') {
            $this->eventClientEnterView($event);
        }

        if ($getEvent == 'clientmoved') {
            $this->eventClientMoved($event);
        }

        if ($getEvent == 'channelcreated') {
            $this->eventChannelCreated($event);
        }

        if ($getEvent == 'channeledited') {
            $this->eventChannelEdited($event);
        }

        if ($getEvent == 'channeldeleted') {
            $this->eventChannelDeleted($event);
        }
    }

    /**
     * @throws Exception
     */
    private function botStopSignal($forceStop = false): void
    {
        $statusSignal = tsServerConfig::query()
            ->where('id', '=', $this->serverId)
            ->first(['is_ts_start'])?->is_ts_start ?? false;

        if ($statusSignal == false || $forceStop == true) {
            tsServerConfig::query()
                ->where('id', '=', $this->serverId)
                ->update([
                    'bot_status_id'=>tsBotLog::SHUTDOWN,
                    'is_ts_start'=>false,
                    'is_active'=>false,
                ]);

            $this->logController->setCustomLog(
                $this->serverId,
                tsBotLog::SHUTDOWN,
                'botStopSignal',
                'Bot stop signal received',
            );

            $this->isBotStop = true;
            $this->reconnectCode = tsServerConfig::BotReconnectFalse;
        }
    }

    private function eventClientEnterView($event): void
    {
        $this->tsVirtualServer->clientListReset();
        $getData = $event->getData();

        try {
            //proof only for clients == 0 and not for query == 1
            if ($getData['client_type'] == 0) {
                $clid = $getData['clid'];
                $nickname = $getData['client_nickname'];
                $badNameResult = false;

                $badNameProtectionActive = tsBotWorkerPolice::query()->where('server_id', '=', $this->serverId)->first();

                if ($badNameProtectionActive?->is_bad_name_protection_active == true) {
                    $badNameController = new BadNameController();
                    $badNameResult = $badNameController->checkBadName($nickname, $this->serverId);
                }

                if ($badNameResult == true) {
                    $kickMsg = 'The nickname is not allowed on this server.';
                    $this->tsVirtualServer->clientPoke($clid, $kickMsg);
                    $this->tsVirtualServer->clientKick($clid, TeamSpeak3::KICK_SERVER, $kickMsg);
                }
            }
        } catch (TeamSpeak3Exception | Exception $e) {
            $this->logController->setCustomLog(
                $this->serverId,
                tsBotLog::TRY_RECONNECT,
                'eventClientEnterView',
                $e->getMessage(),
            );
        }
    }

    private function eventClientMoved($event): void
    {
        try {
            //declare variable
            $getData = $event->getData();
            $ctid = $getData['ctid'];
            $clid = $getData['clid'];

            //proof jobs
            $jobsList = tsBotWorkerChannelsCreate::query()
                ->where('on_cid', '=', $ctid)
                ->where('on_event', '=', 'clientmoved')
                ->where('server_id', '=', $this->serverId)
                ->get();

            //for each entry
            if ($jobsList->count() != 0) {
                $jobFilterCreateChannel = [];
                foreach ($jobsList as $job) {
                    $jobFilterCreateChannel[] = $job->id;
                }

                //has the job a created channel?
                $jobIsCreateChannel = tsBotWorkerChannelsCreate::query()
                    ->whereHas('rel_actions', function ($query) {
                        $query->where('action_bot', 'like', 'create_channel_%');
                    })
                    ->whereIn('id', $jobFilterCreateChannel)
                    ->get();

                if ($jobIsCreateChannel->count() != 0) {
                    foreach ($jobIsCreateChannel as $jobCreateChannel) {
                        //start channels create worker
                        $this->createChannel($jobCreateChannel->id, $this->serverId, $clid);
                    }
                }
            }
        } catch (TeamSpeak3Exception $e) {
            //set log
            $this->logController->setLog($e, tsBotLog::FAILED, 'eventClientMoved');
        }
    }

    private function eventChannelCreated($event): void
    {
        #do nothing
    }

    private function eventChannelEdited($event): void
    {
        try {
            //declare variable
            $getData = $event->getData();
            $cid = $getData['cid'];
            $clid = $getData['invokerid'];
            $cidInfo = $this->tsVirtualServer->channelGetById($cid);
            $channelName = $cidInfo['channel_name']->toString();

            //proof Name
            $badNameController = new BadNameController();
            $badNameResult = $badNameController->checkBadName($channelName, $this->serverId);

            if ($badNameResult == true) {
                $this->tsVirtualServer->channelDelete($cid, true);

                $msg = 'The channel name is not allowed on this server.';
                $this->tsVirtualServer->clientPoke($clid, $msg);
            }
        } catch (TeamSpeak3Exception $e) {
            //set log
            $this->logController->setLog($e, tsBotLog::FAILED, 'eventChannelEdited');
        }
    }

    private function eventChannelDeleted($event): void
    {
        try {
            //declare variable
            $getData = $event->getData();
            $cid = $getData['cid'];

            $this->deleteChannel($cid);
        } catch (TeamSpeak3Exception $e) {
            //set log
            $this->logController->setLog($e, tsBotLog::FAILED, 'eventChannelDeleted');
        }
    }

    private function createChannel(int $jobId, int $serverId, int $clid): void
    {
        try {
            //getJob
            $job = tsBotWorkerChannelsCreate::query()->with([
                'rel_action_user',
                'rel_action',
            ])
                ->where('id', '=', $jobId)
                ->where('server_id', '=', $serverId)
                ->first();

            //if is_active == false, then leave
            if ($job === null || $job->is_active == false) {
                return;
            }

            //create Channel
            //select type of channel / temp - semi - perm / goBackFlag
            switch ($job->rel_action->action_bot) {
                case 'create_channel_temp':
                    $isPerm = false;
                    $isSemi = false;
                    $isGoBackFlag = true;
                    break;
                case 'create_channel_semi':
                    $isPerm = false;
                    $isSemi = true;
                    $isGoBackFlag = false;
                    break;
                case 'create_channel_perm':
                    $isPerm = true;
                    $isSemi = false;
                    $isGoBackFlag = false;
                    break;
                default:
                    $isPerm = false;
                    $isSemi = false;
                    $isGoBackFlag = false;
            }

            //reset list objects
            $this->tsVirtualServer->channelListReset();
            $this->tsVirtualServer->clientListReset();
            $this->tsVirtualServer->channelGroupListReset();
            $this->tsVirtualServer->serverGroupListReset();

            //Client Name
            $client = $this->tsVirtualServer->clientGetById($clid);
            $clientDbId = $client['client_database_id'];

            //get Channel attributes
            $channel = $this->tsVirtualServer->channelGetById($job->on_cid);
            $clientsOnChannel = collect($this->tsVirtualServer->clientList(['cid'=>$job->on_cid]));
            $channelInfo = $channel->getInfo();
            $channelName = $channelInfo['channel_name'];

            //proof is set user a channel with server admin?
            $isOwnChannelExist = false;
            $channelList = collect($this->tsVirtualServer->channelList(['pid'=>$job->on_cid]));

            //ownChannelgroups
            foreach ($channelList->keys()->all() as $channelListCid) {
                //if client in Channel Group
                $ownChannelGroupLists = $this->tsVirtualServer->channelGroupClientList($job->channel_cgid, $channelListCid, $clientDbId);
                //proof own channel is existing
                foreach ($ownChannelGroupLists as $ownChannelGroupList) {
                    if ($ownChannelGroupList['cid'] == $channelListCid && $isOwnChannelExist === false) {
                        //channel exists
                        $isOwnChannelExist = true;
                        //move user to channel
                        $this->tsVirtualServer->clientMove($clid, $channelListCid);

                        if ($isGoBackFlag === true) {
                            //bot goes back in the standard channel
                            $this->tsVirtualServer->clientMove($this->selfClid, $this->standardChannelId);
                        }
                    }
                }
            }
            //if client min count configured
            if ($isOwnChannelExist == false && $job->action_min_clients <= $clientsOnChannel->count()) {
                //proof if channel name available
                $ifAvailable = false;
                $ifMaxChannelReached = false;
                $channelCount = 0;
                $channelDisplayCount = 1;
                $newChannelName = substr($channelName, 0, 37).' '.$channelCount;

                while ($ifAvailable == false) {
                    //set channelname
                    $newChannelName = substr($channelName, 0, 37).'-'.$channelDisplayCount;
                    $channelAvailable = $this->tsVirtualServer->channelList([
                        'channel_name' => $newChannelName,
                    ]);
                    $channelAvailableCount = collect($channelAvailable)->count();

                    if ($channelAvailableCount == 0) {
                        $ifAvailable = true;

                        //if create_max_channels == 0, then unlimited channels can be created
                        if ($channelCount >= $job->create_max_channels && $job->create_max_channels != 0) {
                            $ifMaxChannelReached = true;
                        }
                    } else {
                        $channelCount = $channelCount + 1;
                        $channelDisplayCount = $channelDisplayCount + 1;
                    }
                }

                //if Channel Template is set, then copy the permissions
                if ($ifMaxChannelReached == false && ($job->channel_template_cid != 0 && $job->channel_template_cid != null) == true) {
                    //get channel permissions
                    $templateChannel = tsChannel::query()->where('cid', '=', $job->channel_template_cid)->first();

                    if ($templateChannel !== null) {
                        //create standard channel
                        $createdCid = $this->tsVirtualServer->channelCreate([
                            'channel_name' => $newChannelName,
                            'channel_codec' => $templateChannel->channel_codec,
                            'channel_codec_quality' => $templateChannel->channel_codec_quality,
                            'channel_flag_semi_permanent' => $isSemi,
                            'channel_flag_permanent' => $isPerm,
                            'channel_needed_talk_power' => $templateChannel->channel_needed_talk_power,
                            'channel_flag_maxclients_unlimited' => $templateChannel->channel_flag_maxclients_unlimited,
                            'channel_maxclients' => $templateChannel->channel_maxclients,
                            'channel_flag_maxfamilyclients_inherited' => $templateChannel->channel_flag_maxfamilyclients_inherited,
                            'channel_codec_is_unencrypted' => $templateChannel->channel_codec_is_unencrypted,
                            'cpid' => $job->on_cid,
                        ]);

                        $templatePermission = $this->tsVirtualServer->channelGetById($templateChannel->cid);
                        $templatePermission = $templatePermission->permList();

                        //get created channel
                        $createdChannel = $this->tsVirtualServer->channelGetById($createdCid);

                        //set permissions
                        foreach ($templatePermission as $permission) {
                            $createdChannel->permAssign($permission['permid'], $permission['permvalue']);
                        }
                    }
                } elseif ($ifMaxChannelReached == false) {
                    //create standard channel
                    $createdCid = $this->tsVirtualServer->channelCreate([
                        'channel_name' => $newChannelName,
                        'channel_codec' => 4,
                        'channel_codec_quality' => 6,
                        'channel_flag_semi_permanent' => $isSemi,
                        'channel_flag_permanent' => $isPerm,
                        'cpid' => $job->on_cid,
                    ]);
                }

                //move User in Created Channel
                if (isset($createdCid) && $job->rel_action_user?->action_bot == 'client_move_to_created_channel' && $ifMaxChannelReached == false) {
                    //if client min count configured move all clients in the created channel
                    if ($job->action_min_clients <= $clientsOnChannel->count() && $job->action_min_clients > 1) {
                        foreach ($clientsOnChannel->keys()->all() as $clientId) {
                            $this->tsVirtualServer->clientMove($clientId, $createdCid);
                        }
                    } else {
                        //move the client in the created channel
                        $this->tsVirtualServer->clientMove($clid, $createdCid);
                    }

                    //if channel group id not 0, then set the Channel Group cgid
                    if ($job->channel_cgid != 0) {
                        $this->tsVirtualServer->clientSetChannelGroup($clientDbId, $createdCid, $job->channel_cgid);
                    }
                }

                //if channel temp, then bot go back in the standard channel
                if ($isGoBackFlag == true) {
                    //bot goes back in the standard channel
                    $this->tsVirtualServer->clientMove($this->selfClid, $this->standardChannelId);
                }

                //notify_message_server_group = true
                if ($job->is_notify_message_server_group == true && $ifMaxChannelReached == false) {
                    $notifyClients = collect($this->tsVirtualServer->clientList(['client_servergroups'=>$job->notify_message_server_group_sgid]));
                    //build Message
                    $msg = str_replace(
                        ['{client-name}', '{channel-name}'],
                        [$client->toString(), $channelName],
                        $job->notify_message_server_group_message
                    );

                    foreach ($notifyClients as $notifyClient) {
                        $notifyUser = $this->tsVirtualServer->clientGetById($notifyClient['clid']);

                        if ($job->notify_option == tsBotWorkerChannelsCreate::textMessage) {
                            $notifyUser->message($msg);
                        }

                        if ($job->notify_option == tsBotWorkerChannelsCreate::pokeMessage) {
                            $notifyUser->poke(Str::limit($msg, 97));
                        }
                    }
                }
            }
        } catch(TeamSpeak3Exception $e) {
            //set log
            $this->logController->setLog($e, tsBotLog::FAILED, 'createChannel');
        }
    }

    private function deleteChannel(int $cid): void
    {
        tsChannel::query()
            ->where('server_id', '=', $this->serverId)
            ->where('cid', '=', $cid)
            ->delete();

        tsBotWorkerChannelsCreate::query()
            ->where('server_id', '=', $this->serverId)
            ->where('on_cid', '=', $cid)
            ->delete();

        tsBotWorkerChannelsRemove::query()
            ->where('server_id', '=', $this->serverId)
            ->where('channel_cid', '=', $cid)
            ->delete();
    }

    private function reconnectBot(): int
    {
        if ($this->waitIncrease > 5) {
            $this->logController->setCustomLog(
                $this->serverId,
                tsBotLog::FAILED,
                'reconnectBot',
                'Maximum attempts and waiting time ('.$this->waitTimeSeconds.' seconds) reached',
            );

            return tsServerConfig::BotReconnectFalse;
        } else {
            $this->logController->setCustomLog(
                $this->serverId,
                tsBotLog::TRY_RECONNECT,
                'reconnectBot',
                'New connection attempt in '.$this->waitTimeSeconds.' seconds',
            );

            sleep($this->waitTimeSeconds);
            $this->waitIncrease = $this->waitIncrease + 1;
            //set the default reconnect wait time
            $this->waitTimeSeconds = $this->waitIncrease * 10;

            return tsServerConfig::BotReconnectTrue;
        }
    }

    /**
     * @throws AdapterException
     * @throws TransportException
     * @throws NodeException
     * @throws ServerQueryException
     */
    private function gatherVirtualServerStats(): void
    {
        $this->statisticController->gatherVirtualServerStatistic($this->serverId, $this->tsVirtualServer);
    }

    /**
     * Handle Errors by TeamSpeak3Exception
     * @throws Exception
     */
    private function errorHandlingTeamSpeak3Exception(TeamSpeak3Exception $e): void
    {
        switch ($e->getCode()) {
            case 10061:
                //explanation: server not found
                tsServerConfig::query()
                    ->where('id', '=', $this->serverId)
                    ->update([
                        'bot_status_id'=>tsBotLog::FAILED,
                    ]);

                $this->logController->setLog($e, tsBotLog::FAILED, 'startBot');
                break;
            case 0:
                //connection to server lost will also be triggered if the bot is offline.
                if ($this->isBotStop === false) {
                    $this->reconnectCode = tsServerConfig::BotReconnectTrue;
                } else {
                    $this->reconnectCode = tsServerConfig::BotReconnectFalse;
                }
                break;
            case 513:
                //explanation: queryNickname already in use
                tsServerConfig::query()
                    ->where('id', '=', $this->serverId)
                    ->update([
                        'bot_status_id'=>tsBotLog::TRY_RECONNECT,
                    ]);

                $this->logController->setLog($e, tsBotLog::TRY_RECONNECT, 'startBot');
                //set higher wait time to pretend hanging connections
                $this->waitTimeSeconds = $this->waitIncrease * 60;
                break;
            case 111:
                //explanation: connection refused
                tsServerConfig::query()
                    ->where('id', '=', $this->serverId)
                    ->update([
                        'bot_status_id'=>tsBotLog::FAILED,
                    ]);

                $this->logController->setLog($e, tsBotLog::FAILED, 'startBot');
                break;
            case 113:
                //explanation: no route to host
                tsServerConfig::query()
                    ->where('id', '=', $this->serverId)
                    ->update([
                        'bot_status_id'=>tsBotLog::FAILED,
                    ]);

                $this->logController->setLog($e, tsBotLog::FAILED, 'startBot');
                break;
            default:
                //explanation: unknown Error
                tsServerConfig::query()
                    ->where('id', '=', $this->serverId)
                    ->update([
                        'bot_status_id'=>tsBotLog::FAILED,
                    ]);

                $this->logController->setLog($e, tsBotLog::FAILED, 'startBot');
        }
    }

    /**
     * Handle Errors by Exceptions
     * @throws Exception
     */
    private function errorHandlingException(int $errorCode, string $message): void
    {
        switch ($message) {
            case 'Undefined array key "channel_name"':
                $this->logController->setCustomLog(
                    $this->serverId,
                    tsBotLog::FAILED,
                    'Exception error',
                    $message,
                    $errorCode,
                    $message,
                );
                break;
            default:
                $this->logController->setCustomLog(
                    $this->serverId,
                    tsBotLog::TRY_RECONNECT,
                    'Exception error',
                    'Unknown Exception',
                    $errorCode,
                    $message,
                );
        }
    }
}
