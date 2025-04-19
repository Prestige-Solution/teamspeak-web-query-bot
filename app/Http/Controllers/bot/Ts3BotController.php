<?php

namespace App\Http\Controllers\bot;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sys\Ts3LogController;
use App\Http\Controllers\ts3Config\BadNameController;
use App\Http\Controllers\ts3Config\Ts3ConfigController;
use App\Http\Controllers\ts3Config\Ts3UriStringHelperController;
use App\Models\ts3Bot\ts3BotLog;
use App\Models\ts3Bot\ts3Channel;
use App\Models\ts3Bot\ts3ServerConfig;
use App\Models\ts3BotWorkers\ts3BotWorkerChannelsCreate;
use App\Models\ts3BotWorkers\ts3BotWorkerChannelsRemove;
use App\Models\ts3BotWorkers\ts3BotWorkerPolice;
use Exception;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\Adapter;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TeamSpeak3Exception;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Node;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

class Ts3BotController extends Controller
{
    protected Server|Adapter|Node|Host $ts3_VirtualServer;

    protected Ts3LogController $logController;

    protected int $server_id;

    protected int $waitIncrease;

    protected int $self_clid;

    protected int $standard_channel_id;

    protected int $reconnectCode;

    protected bool $isBotStop = false;

    /**
     * @throws Exception
     */
    public function __construct($server_id)
    {
        $this->server_id = $server_id;
        $this->waitIncrease = 1;
        $this->reconnectCode = ts3ServerConfig::BotReconnectFalse;
        $this->logController = new Ts3LogController('Bot', $this->server_id);

        try {
            TeamSpeak3::init();
        } catch (TeamSpeak3Exception $e) {
            $this->logController->setLog(
                $e,
                ts3BotLog::FAILED,
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
        $this->reconnectCode = ts3ServerConfig::BotReconnectFalse;

        try {
            $ts3ServerConfig = ts3ServerConfig::query()
                ->where('id', '=', $this->server_id)->first();

            if ($ts3ServerConfig->qa_nickname != null) {
                $qaName = $ts3ServerConfig->qa_nickname;
            } else {
                $qaName = $ts3ServerConfig->qa_name;
            }

            //get uri with StringHelper
            $ts3StringHelper = new Ts3UriStringHelperController();
            $uri = $ts3StringHelper->getStandardUriString(
                $ts3ServerConfig->qa_name,
                $ts3ServerConfig->qa_pw,
                $ts3ServerConfig->server_ip,
                $ts3ServerConfig->server_query_port,
                $ts3ServerConfig->server_port,
                $qaName,
                $this->server_id,
                $ts3ServerConfig->mode
            );

            $this->ts3_VirtualServer = TeamSpeak3::factory($uri);

            $whoami = $this->ts3_VirtualServer->whoami();
            $this->self_clid = $whoami['client_id'];
            $this->standard_channel_id = $whoami['client_channel_id'];

            Signal::getInstance()->subscribe('serverqueryWaitTimeout', [$this, 'checkKeepAlive']);
            Signal::getInstance()->subscribe('notifyEvent', [$this, 'EventListener']);

            $this->ts3_VirtualServer->serverGetSelected()->notifyRegister('server');
            $this->ts3_VirtualServer->serverGetSelected()->notifyRegister('channel');

            ts3ServerConfig::query()->where('id', '=', $this->server_id)->update([
                'bot_status_id'=> ts3BotLog::RUNNING,
            ]);

            $this->logController->setCustomLog(
                $this->server_id,
                ts3BotLog::RUNNING,
                'Starting bot',
                'Bot has been started. Wait for events',
            );

            $this->waitIncrease = 1;

            while (1) {
                $this->ts3_VirtualServer->getAdapter()->wait();
            }
        } catch(TeamSpeak3Exception $e) {
            $this->errorHandlingTeamSpeak3Exception($e);
        } catch (Exception $e) {
            $this->errorHandlingException($e->getCode(), $e->getMessage());
        } finally {
            if ($this->reconnectCode == ts3ServerConfig::BotReconnectTrue) {
                $this->logController->setCustomLog(
                    $this->server_id,
                    ts3BotLog::TRY_RECONNECT,
                    'Restartig bot',
                    'Bot will be restarted',
                );

                $this->startBot();
            }

            //is bot has max re-connect times
            if ($this->reconnectCode == ts3ServerConfig::BotReconnectFalse && $this->isBotStop === false) {
                $this->logController->setCustomLog(
                    $this->server_id,
                    ts3BotLog::TRY_RECONNECT,
                    'Restartig bot',
                    'Bot cant reconnect or restart!',
                );

                $this->botStopSignal(true);
            }

            if ($this->isBotStop === true) {
                $this->logController->setCustomLog(
                    $this->server_id,
                    ts3BotLog::SUCCESS,
                    'Stopping bot',
                    'Bot was stopped',
                );
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

        //check bot stop
        $this->botStopSignal();

        if ($this->isBotStop === false) {
            try {
                $keepAliveStatus = $this->ts3_VirtualServer->getAdapter()->request('clientupdate');
                $keepAliveStatus->toArray();

                if ($keepAliveStatus->getErrorProperty('msg') != 'ok') {
                    $this->logController->setCustomLog(
                        $this->server_id,
                        ts3BotLog::FAILED,
                        'Check Keep Alive',
                        'Bot is dead! Restart Bot',
                    );

                    ts3ServerConfig::query()
                        ->where('id', '=', $this->server_id)
                        ->update([
                            'bot_status_id'=>ts3BotLog::TRY_RECONNECT,
                        ]);
                }
            } catch (TeamSpeak3Exception $e) {
                $this->logController->setLog($e, ts3BotLog::FAILED, 'Check Keep Alive');
            }
        }
    }

    /**
     * @throws Exception
     */
    public function EventListener($event): void
    {
        $this->botStopSignal();
        $getEvent = $event->getType()->toString();

        if (config('app.bot_debug') == true) {
            // print message
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
        $statusSignal = ts3ServerConfig::query()
            ->where('id', '=', $this->server_id)
            ->first(['is_ts3_start'])->is_ts3_start;

        if ($statusSignal == false || $forceStop == true) {
            ts3ServerConfig::query()
                ->where('id', '=', $this->server_id)
                ->update([
                    'bot_status_id'=>ts3BotLog::STOPPED,
                    'is_ts3_start'=>false,
                    'is_active'=>false,
                ]);

            $this->logController->setCustomLog(
                $this->server_id,
                ts3BotLog::STOPPED,
                'Stopping bot',
                'Bot will be stopped',
            );

            $this->isBotStop = true;
            $this->reconnectCode = ts3ServerConfig::BotReconnectFalse;

            $this->ts3_VirtualServer->getParent()->getAdapter()->getTransport()->disconnect();
        }
    }

    private function eventClientEnterView($event): void
    {
        $this->ts3_VirtualServer->clientListReset();
        $getData = $event->getData($event);

        try {
            //proof only for clients == 0 and not for query == 1
            if ($getData['client_type'] == 0) {
                $getCLID = $getData['clid'];
                $getNickname = $getData['client_nickname'];
                $badNameResult = false;

                $badNameProtectionActive = ts3BotWorkerPolice::query()->where('server_id', '=', $this->server_id)->first();

                if ($badNameProtectionActive->is_bad_name_protection_active == true) {
                    $badNameController = new BadNameController();
                    $badNameResult = $badNameController->checkBadName($getNickname, $this->server_id);
                }

                if ($badNameResult == true) {
                    $kickMsg = 'The nickname is not allowed on this server.';
                    $this->ts3_VirtualServer->clientPoke($getCLID, $kickMsg);
                    $this->ts3_VirtualServer->clientKick($getCLID, TeamSpeak3::KICK_SERVER, $kickMsg);
                }
            }
        } catch (TeamSpeak3Exception | Exception $e) {
            $this->logController->setCustomLog(
                $this->server_id,
                ts3BotLog::TRY_RECONNECT,
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
            $getCTID = $getData['ctid'];
            $getCLID = $getData['clid'];

            //proof jobs
            $jobsList = ts3BotWorkerChannelsCreate::query()
                ->where('on_cid', '=', $getCTID)
                ->where('on_event', '=', 'clientmoved')
                ->where('server_id', '=', $this->server_id)
                ->get();

            //for each entry
            if ($jobsList->count() != 0) {
                $jobFilterCreateChannel = [];
                foreach ($jobsList as $jobIds) {
                    $jobFilterCreateChannel[] = $jobIds->id;
                }

                //has the job a created channel?
                $jobIsCreateChannel = ts3BotWorkerChannelsCreate::query()
                    ->whereHas('rel_actions', function ($query) {
                        $query->where('action_bot', 'like', 'create_channel_%');
                    })
                    ->whereIn('id', $jobFilterCreateChannel)
                    ->get();

                if ($jobIsCreateChannel->count() != 0) {
                    foreach ($jobIsCreateChannel as $jobCreateChannel) {
                        //start channel create worker
                        $this->createChannel($jobCreateChannel->id, $this->server_id, $getCLID);
                    }
                }
            }
        } catch (TeamSpeak3Exception $e) {
            //set log
            $this->logController->setLog($e, ts3BotLog::FAILED, 'eventClientMoved');
        }
    }

    private function eventChannelCreated($event): void
    {
        try {
            //declare variable
            $getData = $event->getData($event);
            $getCID = $getData['cid'];
            $this->storeCreatedChannel($getCID);
        } catch (TeamSpeak3Exception $e) {
            //set log
            $this->logController->setLog($e, ts3BotLog::FAILED, 'eventChannelCreated');
        }
    }

    private function eventChannelEdited($event): void
    {
        try {
            //declare variable
            $getData = $event->getData($event);
            $getCID = $getData['cid'];
            $getCLID = $getData['invokerid'];
            $getCIDInfo = $this->ts3_VirtualServer->channelGetById($getCID);
            $getChannelName = $getCIDInfo['channel_name']->tostring();

            //proof Name
            $badNameController = new BadNameController();
            $badNameResult = $badNameController->checkBadName($getChannelName, $this->server_id);

            if ($badNameResult == true) {
                $this->ts3_VirtualServer->channelDelete($getCID, true);

                $msg = 'The channel name is not allowed on this server.';
                $this->ts3_VirtualServer->clientPoke($getCLID, $msg);
            } else {
                $this->updateChannel($getCID);
            }
        } catch (TeamSpeak3Exception $e) {
            //set log
            $this->logController->setLog($e, ts3BotLog::FAILED, 'eventChannelEdited');
        }
    }

    private function eventChannelDeleted($event): void
    {
        try {
            //declare variable
            $getData = $event->getData($event);
            $getCID = $getData['cid'];

            $this->deleteChannel($getCID);
        } catch (TeamSpeak3Exception $e) {
            //set log
            $this->logController->setLog($e, ts3BotLog::FAILED, 'eventChannelDeleted');
        }
    }

    private function createChannel($jobID, $server_id, $clid): void
    {
        try {
            //getJob
            $job = ts3BotWorkerChannelsCreate::query()->with([
                'rel_action_user',
                'rel_action',
            ])
                ->where('id', '=', $jobID)
                ->where('server_id', '=', $server_id)
                ->first();

            //if is_active == false then leave
            if ($job->is_active == false) {
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
            $this->ts3_VirtualServer->channelListReset();
            $this->ts3_VirtualServer->clientListReset();
            $this->ts3_VirtualServer->channelGroupListReset();
            $this->ts3_VirtualServer->serverGroupListReset();

            //Client Name
            $clName = $this->ts3_VirtualServer->clientGetById($clid);
            $clDbID = $clName['client_database_id'];

            //get Channel attributes
            $chGetByID = $this->ts3_VirtualServer->channelGetById($job->on_cid);
            $clientsOnChannel = collect($this->ts3_VirtualServer->clientList(['cid'=>$job->on_cid]));
            $chInfo = $chGetByID->getInfo();
            $chName = $chInfo['channel_name']->toString();

            //proof ist set user a channel with server admin?
            $isOwnChannelExist = false;
            $channelList = collect($this->ts3_VirtualServer->channelList(['pid'=>$job->on_cid]));

            //ownChannelgroups
            foreach ($channelList->keys()->all() as $channelListCID) {
                //if client in Channel Group
                $ownChannelGroupLists = $this->ts3_VirtualServer->channelGroupClientList($job->channel_cgid, $channelListCID, $clDbID);
                //proof own channel is existing
                foreach ($ownChannelGroupLists as $ownChannelGrouplist) {
                    if ($ownChannelGrouplist['cid'] == $channelListCID) {
                        //channel is exist
                        $isOwnChannelExist = true;
                        //move user to channel
                        $this->ts3_VirtualServer->clientMove($clid, $channelListCID);

                        if ($isGoBackFlag == true) {
                            //bot go back in standard channel
                            $this->ts3_VirtualServer->clientMove($this->self_clid, $this->standard_channel_id);
                        }
                    }
                }
            }

            //if client min count configured
            if ($job->action_min_clients <= $clientsOnChannel->count() && $isOwnChannelExist == false) {
                //proof if channel name available
                $ifAvailable = false;
                $ifMaxChannelReached = false;
                $channelCount = 0;
                $channelDisplayCount = 1;
                $newChannelname = substr($chName, 0, 37).' '.$channelCount;

                while ($ifAvailable == false) {
                    //set channelname
                    $newChannelname = substr($chName, 0, 37).'-'.$channelDisplayCount;
                    $channelAvailable = $this->ts3_VirtualServer->channelList([
                        'channel_name' => $newChannelname,
                    ]);
                    $channelAvailableCount = collect($channelAvailable)->count();

                    if ($channelAvailableCount == 0) {
                        $ifAvailable = true;

                        //if create_max_channels == 0 then unlimited channels can be created
                        if ($channelCount >= $job->create_max_channels && $job->create_max_channels != 0) {
                            $ifMaxChannelReached = true;
                        }
                    } else {
                        $channelCount = $channelCount + 1;
                        $channelDisplayCount = $channelDisplayCount + 1;
                    }
                }

                //if Channel Template is set then copy the permissions
                if ($ifMaxChannelReached == false && ($job->channel_template_cid != 0 || $job->channel_template_cid != null) == true) {
                    //get channel permissions
                    $templateChannel = ts3Channel::query()->where('cid', '=', $job->channel_template_cid)->first();

                    //create standard channel
                    $createdCID = $this->ts3_VirtualServer->channelCreate([
                        'channel_name' => $newChannelname,
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

                    $templatePermission = $this->ts3_VirtualServer->channelGetById($templateChannel->cid);
                    $templatePermission = $templatePermission->permList();

                    //get created channel
                    $createdChannel = $this->ts3_VirtualServer->channelGetById($createdCID);

                    //set permissions
                    foreach ($templatePermission as $permission) {
                        $createdChannel->permAssign($permission['permid'], $permission['permvalue']);
                    }
                } elseif ($ifMaxChannelReached == false) {
                    //create standard channel
                    $createdCID = $this->ts3_VirtualServer->channelCreate([
                        'channel_name' => $newChannelname,
                        'channel_codec' => 4,
                        'channel_codec_quality' => 6,
                        'channel_flag_semi_permanent' => $isSemi,
                        'channel_flag_permanent'=>$isPerm,
                        'cpid' => $job->on_cid,
                    ]);
                }

                //move User in Created Channel
                if ($job->rel_action_user->action_bot == 'client_move_to_created_channel' && $ifMaxChannelReached == false) {
                    //if client min count configured move all clients in created channel
                    if ($job->action_min_clients <= $clientsOnChannel->count() && $job->action_min_clients > 1) {
                        foreach ($clientsOnChannel->keys()->all() as $clientID) {
                            $this->ts3_VirtualServer->clientMove($clientID, $createdCID);
                        }
                    } else {
                        //move client in created channel
                        $this->ts3_VirtualServer->clientMove($clid, $createdCID);
                    }

                    //if channel group id not 0 then set the Channel Group cgid
                    if ($job->channel_cgid != 0) {
                        $this->ts3_VirtualServer->clientSetChannelGroup($clDbID, $createdCID, $job->channel_cgid);
                    }
                }

                //if channel temp then bot go back in standard channel
                if ($isGoBackFlag == true) {
                    //bot go back in standard channel
                    $this->ts3_VirtualServer->clientMove($this->self_clid, $this->standard_channel_id);
                }

                //notify_message_server_group = true
                if ($job->is_notify_message_server_group == true && $ifMaxChannelReached == false) {
                    $notifyClients = collect($this->ts3_VirtualServer->clientList(['client_servergroups'=>$job->notify_message_server_group_sgid]));
                    //build Message
                    $msg = str_replace(
                        ['{client-name}', '{channel-name}'],
                        [$clName->toString(), $chName],
                        $job->notify_message_server_group_message
                    );

                    foreach ($notifyClients as $notifyClient) {
                        $notifyUser = $this->ts3_VirtualServer->clientGetById($notifyClient['clid']);
                        $notifyUser->message($msg);
                    }
                }
            }
        } catch(TeamSpeak3Exception $e) {
            //set log
            $this->logController->setLog($e, ts3BotLog::FAILED, 'createChannel');
        }
    }

    private function storeCreatedChannel($cid): void
    {
        $autoUpdateActive = ts3BotWorkerPolice::query()->where('server_id', '=', $this->server_id)->first()->is_channel_auto_update_active;

        if ($autoUpdateActive == true) {
            try {
                //reset channel list
                $this->ts3_VirtualServer->channelListReset();
                //get channel by id
                $channel = $this->ts3_VirtualServer->channelGetById($cid);
                //get channel info
                $channelInfo = $channel->getInfo();
                //store info
                $ts3ConfigController = new Ts3ConfigController();
                $ts3ConfigController->createChannels($this->server_id, $channelInfo, $channel->toString());
            } catch (TeamSpeak3Exception $e) {
                //set log
                $this->logController->setLog($e, ts3BotLog::FAILED, 'storeCreatedChannel');
            }
        }
    }

    private function updateChannel(int $cid): void
    {
        //check auto Update ist active
        $autoUpdateActive = ts3BotWorkerPolice::query()->where('server_id', '=', $this->server_id)->first()->is_channel_auto_update_active;

        if ($autoUpdateActive == true) {
            try {
                //reset channel list
                $this->ts3_VirtualServer->channelListReset();
                //get channel by id
                $channel = $this->ts3_VirtualServer->channelGetById($cid);
                //get channel info
                $channelInfo = $channel->getInfo();
                //store info
                $ts3ConfigController = new Ts3ConfigController();
                $ts3ConfigController->updateChannels($this->server_id, $channelInfo, $channel->toString(), $cid);
            } catch (TeamSpeak3Exception $e) {
                //set log
                $this->logController->setLog($e, ts3BotLog::FAILED, 'updateChannel');
            }
        }
    }

    private function deleteChannel($cid): void
    {
        ts3Channel::query()
            ->where('server_id', '=', $this->server_id)
            ->where('cid', '=', $cid)
            ->delete();

        ts3BotWorkerChannelsCreate::query()
            ->where('server_id', '=', $this->server_id)
            ->where('on_cid', '=', $cid)
            ->delete();

        ts3BotWorkerChannelsRemove::class::query()
            ->where('server_id', '=', $this->server_id)
            ->where('channel_cid', '=', $cid)
            ->delete();
    }

    private function reconnectBot(): int
    {
        //set custom log
        $this->logController->setCustomLog(
            $this->server_id,
            ts3BotLog::TRY_RECONNECT,
            'reconnectBot',
            'An attempt is made to reconnect the bot.',
        );

        $waitTimeSeconds = 10 * $this->waitIncrease;

        sleep($waitTimeSeconds);

        $this->waitIncrease = $this->waitIncrease + 1;

        if ($this->waitIncrease >= 5) {
            //set custom log
            $this->logController->setCustomLog(
                $this->server_id,
                ts3BotLog::FAILED,
                'reconnectBot',
                'Maximum attempts and waiting time ('.$waitTimeSeconds.' seconds) reached',
            );

            return ts3ServerConfig::BotReconnectFalse;
        } else {
            //set custom log
            $this->logController->setCustomLog(
                $this->server_id,
                ts3BotLog::TRY_RECONNECT,
                'reconnectBot',
                'New connection attempt in '.$waitTimeSeconds.' seconds',
            );

            return ts3ServerConfig::BotReconnectTrue;
        }
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
                ts3ServerConfig::query()
                    ->where('id', '=', $this->server_id)
                    ->update([
                        'bot_status_id'=>ts3BotLog::FAILED,
                        'is_ts3_start'=>false,
                        'is_active'=>false,
                    ]);

                $this->logController->setLog($e, ts3BotLog::FAILED, 'startBot');
                $this->botStopSignal(true);
                break;
            case 0:
                //connection to server lost will also be triggered if the bot is stopped.
                //code 0 is also already fine and we do not handle it as error.
                //this comment is also a reminder
                break;
            case 513:
                //explanation: queryNickname already in use
                ts3ServerConfig::query()
                    ->where('id', '=', $this->server_id)
                    ->update([
                        'bot_status_id'=>ts3BotLog::TRY_RECONNECT,
                    ]);

                $this->logController->setLog($e, ts3BotLog::TRY_RECONNECT, 'startBot');
                $this->reconnectCode = $this->reconnectBot();
                break;
            case 111:
                //explanation: connection refused
                ts3ServerConfig::query()
                    ->where('id', '=', $this->server_id)
                    ->update([
                        'bot_status_id'=>ts3BotLog::FAILED,
                        'is_ts3_start'=>false,
                        'is_active'=>false,
                    ]);

                $this->logController->setLog($e, ts3BotLog::FAILED, 'startBot');
                $this->botStopSignal(true);
                break;
            case 113:
                //explanation: no route to host
                ts3ServerConfig::query()
                    ->where('id', '=', $this->server_id)
                    ->update([
                        'bot_status_id'=>ts3BotLog::FAILED,
                        'is_ts3_start'=>false,
                        'is_active'=>false,
                    ]);

                $this->logController->setLog($e, ts3BotLog::FAILED, 'startBot');
                $this->botStopSignal(true);
                break;
            default:
                //explanation: unknown Error
                ts3ServerConfig::query()
                    ->where('id', '=', $this->server_id)
                    ->update([
                        'bot_status_id'=>ts3BotLog::FAILED,
                    ]);

                $this->logController->setLog($e, ts3BotLog::FAILED, 'startBot');
                $this->reconnectCode = $this->reconnectBot();
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
                    $this->server_id,
                    ts3BotLog::FAILED,
                    'Exception error',
                    $message,
                    $errorCode,
                    $message,
                );
                break;
            default:
                $this->logController->setCustomLog(
                    $this->server_id,
                    ts3BotLog::TRY_RECONNECT,
                    'Exception error',
                    'Unknown Exception',
                    $errorCode,
                    $message,
                );
        }

        $this->botStopSignal(true);
    }
}
