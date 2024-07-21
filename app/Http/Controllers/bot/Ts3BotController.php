<?php

namespace App\Http\Controllers\bot;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sys\Ts3LogController;
use App\Http\Controllers\ts3Config\BadNameController;
use App\Http\Controllers\ts3Config\Ts3ConfigController;
use App\Models\ts3Bot\ts3Channel;
use App\Models\ts3Bot\ts3ServerConfig;
use App\Models\ts3BotJobs\ts3BotJobCreateChannels;
use App\Models\ts3BotWorkers\ts3BotWorkerChannelRemover;
use App\Models\ts3BotWorkers\ts3BotWorkerPolice;
use Illuminate\Support\Facades\Crypt;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal as TS3_Signal;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\Adapter;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TeamSpeak3Exception;

class Ts3BotController extends Controller
{
    protected Server|Adapter|Host $ts3_VirtualServer;
    protected mixed $TS3PHPFramework;
    protected Ts3LogController $logController;
    protected int $serverPort;
    protected int $serverID;
    protected int $waitIncrease;
    protected int $self_clid;
    protected int $standard_channel_id;
    protected int $reconnectCode;
    protected bool $botStop = false;

    /**
     * @throws \Exception
     */
    public function __construct($serverID)
    {
        //get Server config
        $ts3ServerConfig = ts3ServerConfig::query()
            ->where('id','=', $serverID)->first();

        //declare logging
        $this->serverID = $ts3ServerConfig->id;
        $this->logController = new Ts3LogController('ts3Bot', $this->serverID);

        //declare ts3Framework
        $TS3PHPFramework = new TeamSpeak3();
        try {
            TeamSpeak3::init();
        } catch (\Exception $e) {
            $this->logController->setLog(
                $e->getMessage(),
                4,
                'Construct Start Bot'
            );
        }

        //set global variables
        $this->TS3PHPFramework = $TS3PHPFramework;
        $this->serverPort = $ts3ServerConfig->server_port;
        $this->waitIncrease = 1;
        $this->reconnectCode = ts3ServerConfig::BotReconnectFalse;

        //start bot
        $this->startBot();
    }

    /**
     * @throws \Exception
     */
    public function startBot(): void
    {
        //set restart no
        $this->reconnectCode = ts3ServerConfig::BotReconnectFalse;

        try {
            //get Server config
            $ts3ServerConfig = ts3ServerConfig::query()
                ->where('id','=', $this->serverID)->first();

            if ($ts3ServerConfig->qa_nickname != NULL)
            {
                $qaNickname = $ts3ServerConfig->qa_nickname;
            }else
            {
                $qaNickname = $ts3ServerConfig->qa_name;
            }

            // Connect via server_ip to ts3 Server // timeout in seconds
            $uri = 'serverquery://'
                .$ts3ServerConfig->qa_name.':'.Crypt::decryptString($ts3ServerConfig->qa_pw).
                '@'.$ts3ServerConfig->server_ip.
                ':'.$ts3ServerConfig->server_query_port.
                '/?server_port='.$ts3ServerConfig->server_port.
                '&timeout=30'.
                '&no_query_clients'.
                '&blocking=0'.
                '&nickname='.$qaNickname;

            // connect to above specified server, authenticate and spawn an object for the virtual server on port 9987
            $this->ts3_VirtualServer = $this->TS3PHPFramework->factory($uri);
            //get my current user clid
            $whoami= $this->ts3_VirtualServer->whoami();
            $this->self_clid = $whoami['client_id'];
            $this->standard_channel_id = $whoami['client_channel_id'];

            //action when bot is timeout
            TS3_Signal::getInstance()->subscribe("serverqueryWaitTimeout", array($this, "checkKeepAlive"));
            //this is every Event listening
            TS3_Signal::getInstance()->subscribe("notifyEvent", array($this, "EventListener"));
            TS3_Signal::getInstance()->subscribe("notifyTextmessage", array($this, "EventListenerTextmassageOnServer"));

            //declare notify
            $this->ts3_VirtualServer->serverGetSelected()->notifyRegister("server");
            $this->ts3_VirtualServer->serverGetSelected()->notifyRegister("channel");
            $this->ts3_VirtualServer->serverGetSelected()->notifyRegister("textserver");

            //set status bot is running
            ts3ServerConfig::query()->where('id','=',$this->serverID)->update([
                'bot_status_id'=>1,
            ]);

            //set custom log
            $this->logController->setCustomLog(
                $this->serverID,
                1,
                'Bot start process',
                'Bot wurde gestartet. Warte auf Events',
            );

            //set reset wait Increase
            $this->waitIncrease = 1;

            //wait for signals
            while (1)
            {
                $this->ts3_VirtualServer->getAdapter()->wait();
            }
        }
        catch(TeamSpeak3Exception $e)
        {
            switch ($e->getCode())
            {
                case 10061:
                    //server not found
                    ts3ServerConfig::query()
                        ->where('id','=',$this->serverID)
                        ->update([
                            'bot_status_id'=>4,
                            'ts3_start_stop'=>0,
                            'active'=>0,
                        ]);
                    //set log
                    $this->logController->setLog($e->getMessage(),4,'startBot');
                    //stop bot process
                    $this->botStopSignal(true);
                    break;
                case 0:
                    //connection to server lost
                    ts3ServerConfig::query()
                        ->where('id','=',$this->serverID)
                        ->update([
                            'bot_status_id'=>2,
                        ]);
                    //set log
                    $this->logController->setLog($e->getMessage(),2,'startBot');
                    //try restart
                    $this->reconnectCode = $this->reconnectBot();
                    break;
                case 513:
                    //queryNickname already in use
                    ts3ServerConfig::query()
                        ->where('id','=',$this->serverID)
                        ->update([
                            'bot_status_id'=>2,
                        ]);
                    //set log
                    $this->logController->setLog($e->getMessage(),2,'startBot');
                    //try restart
                    $this->reconnectCode = $this->reconnectBot();
                    break;
                case 111:
                    //connection refused
                case 113:
                    //no route to host
                    ts3ServerConfig::query()
                        ->where('id','=',$this->serverID)
                        ->update([
                            'bot_status_id'=>4,
                            'ts3_start_stop'=>0,
                            'active'=>0,
                        ]);
                    //set log
                    $this->logController->setLog($e->getMessage(),4,'startBot');
                    //stop bot process
                    $this->botStopSignal(true);
                    break;
                default:
                    //unknown Error
                    ts3ServerConfig::query()
                        ->where('id','=',$this->serverID)
                        ->update([
                            'bot_status_id'=>4,
                        ]);
                    //set log
                    $this->logController->setLog($e->getMessage(),4,'startBot');
            }
        }catch (\Exception $e)
        {
            switch ($e->getMessage())
            {
                //custom throw bot stop signal or other exceptions
                case 'BotStopException':
                    //set custom log
                    $this->logController->setCustomLog(
                        $this->serverID,
                        4,
                        'Handling BotStop Exception',
                        'Bot wird gestoppt',
                        null,
                        'stopping bot',
                    );

                    //set botStop true
                    $this->botStop = true;
                    break;
                case 'Undefined array key "channel_name"':
                    //set custom log
                    $this->logController->setCustomLog(
                        $this->serverID,
                        4,
                        'Handling unknown exceptions',
                        'Unknown TS3 Exception - bot stopped',
                        $e->getCode(),
                        $e->getMessage(),
                    );
                    break;
                default:
                    //set custom log
                    $this->logController->setCustomLog(
                        $this->serverID,
                        2,
                        'Handling undefined exceptions',
                        'Undefined TS3 Exception',
                        $e->getCode(),
                        $e->getMessage(),
                    );
            }

        } finally
        {
            //if bot has re-connect code
            if($this->reconnectCode == ts3ServerConfig::BotReconnectTrue)
            {
                //set custom log
                $this->logController->setCustomLog(
                    $this->serverID,
                    2,
                    'Handling finally restart bot',
                    'Bot wird neu gestartet',
                );

                //restart bot
                $this->startBot();
            }

            //is bot has max re-connect times
            if ($this->reconnectCode == ts3serverConfig::BotReconnectFalse)
            {
                //set custom log
                $this->logController->setCustomLog(
                    $this->serverID,
                    2,
                    'Handling finally restart bot failed',
                    'Bot wird gestoppt',
                );

                //bot stop Process
                $this->botStopSignal(true);
            }

            //if bot stopped true
            if($this->botStop == true)
            {
                //set custom log
                $this->logController->setCustomLog(
                    $this->serverID,
                    5,
                    'Bot stop process finally finished',
                    'Bot wurde gestoppt',
                );
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function checkKeepAlive(): void
    {
        if (config('app.bot_debug') == true)
        {
            // print the error message returned by the server
            echo "Check bot is alive \n";
        }

        //check bot stop
        $this->botStopSignal();

        try {
            $keepAliveStatus = $this->ts3_VirtualServer->getAdapter()->request('clientupdate');
            $keepAliveStatus->toArray();

            if ($keepAliveStatus->getErrorProperty('msg') != 'ok')
            {
                //set custom log
                $this->logController->setCustomLog(
                    $this->serverID,
                    4,
                    'checkKeepAlive',
                    'Bot is dead! Restart Bot',
                );

                ts3ServerConfig::query()
                    ->where('id','=',$this->serverID)
                    ->update([
                        'bot_status_id'=>2,
                    ]);

                $this->startBot();
            }
        }catch (TeamSpeak3Exception $e)
        {
            //set log
            $this->logController->setLog($e->getMessage(),4,'checkKeepAlive');
        }
    }

    /**
     * @throws \Exception
     */
    public function EventListener($event): void
    {
        //check bot stop
        $this->botStopSignal();

        //get event type
        $getEvent = $event->getType()->toString();

        if (config('app.bot_debug') == true)
        {
            // print message
            echo 'type: '.$getEvent."\n";
        }

        if ($getEvent == 'cliententerview')
        {
            $this->eventClientEnterView($event);
        }

        if ($getEvent == 'clientmoved')
        {
           $this->eventClientMoved($event);
        }

        if ($getEvent == 'channelcreated')
        {
            $this->eventChannelCreated($event);
        }

        if ($getEvent == 'channeledited')
        {
            $this->eventChannelEdited($event);
        }

        if ($getEvent == 'channeldeleted')
        {
            $this->eventChannelDeleted($event);
        }
    }
    public function EventListenerTextmassageOnServer($event)
    {

    }

    /**
     * @throws \Exception
     */
    private function botStopSignal($forceStop = false): void
    {
        //check bot stop
        $statusSignal = ts3ServerConfig::query()
            ->where('id','=',$this->serverID)
            ->first(['ts3_start_stop'])->ts3_start_stop;

        //false = stop
        if ($statusSignal == false || $forceStop == true)
        {
            ts3ServerConfig::query()
                ->where('id','=',$this->serverID)
                ->update([
                    'bot_status_id'=>3,
                    'ts3_start_stop'=>0,
                    'active'=>0,
                ]);

            //set custom log
            $this->logController->setCustomLog(
                $this->serverID,
                3,
                'botStopSignal',
                'Bot wird gestoppt',
            );

            //set signal to true
            $this->botStop = true;

            //Stop Bot and throw a new exception
            $this->ts3_VirtualServer->getAdapter()->getTransport()->disconnect();
            throw new \Exception('BotStopException');
        }
    }

    private function eventClientEnterView($event): void
    {
        //declare variable
        $this->ts3_VirtualServer->clientListReset();
        $getData = $event->getData($event);

        try {
            //proof only for clients == 0 and not for query == 1
            if ($getData['client_type'] == 0)
            {
                $getCLID = $getData['clid'];
                $getNickname = $getData['client_nickname'];
                $badNameResult = false;

                //get active Status
                $badNameProtectionActive = ts3BotWorkerPolice::query()->where('server_id','=',$this->serverID)->first();

                if ($badNameProtectionActive->bad_name_protection_active == true)
                {
                    $badNameController = new BadNameController();
                    $badNameResult = $badNameController->checkBadName($getNickname,$this->serverID);
                }

                //if true then kick from server
                if ($badNameResult == true)
                {
                    $kickMsg = 'Der Nickname ist auf diesem Server nicht erlaubt!';
                    $this->ts3_VirtualServer->clientPoke($getCLID,$kickMsg);
                    $this->ts3_VirtualServer->clientKick($getCLID,TeamSpeak3::KICK_SERVER, $kickMsg);
                }
            }
        }catch (TeamSpeak3Exception | \Exception $e)
        {
            //set custom log
            $this->logController->setCustomLog(
                $this->serverID,
                2,
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
            $jobsList = ts3BotJobCreateChannels::query()
                ->where('on_cid','=',$getCTID)
                ->where('on_event','=','clientmoved')
                ->where('server_id','=', $this->serverID)
                ->get();

            //for each entry
            if($jobsList->count() != 0)
            {
                $jobFilterCreateChannel = array();
                foreach ($jobsList as $jobIds)
                {
                    $jobFilterCreateChannel[] = $jobIds->id;
                }

                //has the job a created channel?
                $jobIsCreateChannel = ts3BotJobCreateChannels::query()
                    ->whereHas('rel_actions',function($query){
                        $query->where('action_bot', 'like','create_channel_%');
                    })
                    ->whereIn('id',$jobFilterCreateChannel)
                    ->get();

                if ($jobIsCreateChannel->count() != 0)
                {
                    foreach ($jobIsCreateChannel as $jobCreateChannel)
                    {
                        //start channel create worker
                        $this->createChannel($jobCreateChannel->id, $this->serverID, $getCLID);
                    }
                }
            }
        }catch (TeamSpeak3Exception $e)
        {
            //set log
            $this->logController->setLog($e->getMessage(),4,'eventClientMoved');
        }
    }

    private function eventChannelCreated($event): void
    {
        try {
            //declare variable
            $getData = $event->getData($event);
            $getCID = $getData['cid'];
            $this->storeCreatedChannel($getCID);

        }catch (TeamSpeak3Exception $e)
        {
            //set log
            $this->logController->setLog($e->getMessage(),4,'eventChannelCreated');
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
            $badNameResult = $badNameController->checkBadName($getChannelName, $this->serverID);

            if ($badNameResult == true)
            {
                $this->ts3_VirtualServer->channelDelete($getCID, true);

                $msg = 'Der Channelname ist auf diesem Server nicht erlaubt!';
                $this->ts3_VirtualServer->clientPoke($getCLID,$msg);
            }else
            {
                $this->updateChannel($getCID);
            }
        }catch (TeamSpeak3Exception $e)
        {
            //set log
            $this->logController->setLog($e->getMessage(),4,'eventChannelEdited');
        }

    }

    private function eventChannelDeleted($event): void
    {
        try {
            //declare variable
            $getData = $event->getData($event);
            $getCID = $getData['cid'];

            $this->deleteChannel($getCID);
        }catch (TeamSpeak3Exception $e)
        {
            //set log
            $this->logController->setLog($e->getMessage(),4,'eventChannelDeleted');
        }
    }

    private function createChannel($jobID, $serverID, $clid): void
    {
        try
        {
            //getJob
            $job = ts3BotJobCreateChannels::query()->with([
                'rel_action_user',
                'rel_action',
            ])
                ->where('id','=', $jobID)
                ->where('server_id','=',$serverID)
                ->first();

            //create Channel
            //select type of channel / temp - semi - perm / goBackFlag
            switch ($job->rel_action->action_bot)
            {
                case 'create_channel_temp':
                    $boolPerm = false;
                    $boolSemi = false;
                    $goBackFlag = true;
                    break;
                case 'create_channel_semi':
                    $boolPerm = false;
                    $boolSemi = true;
                    $goBackFlag = false;
                    break;
                case 'create_channel_perm':
                    $boolPerm = true;
                    $boolSemi = false;
                    $goBackFlag = false;
                    break;
                default:
                    $boolPerm = false;
                    $boolSemi = false;
                    $goBackFlag = false;
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
            $ownChannelExistBool = false;
            $channelList = collect($this->ts3_VirtualServer->channelList(['pid'=>$job->on_cid]));

            //ownChannelgroups
            foreach ($channelList->keys()->all() as $channelListCID)
            {
                //if client in Channel Group
                $ownChannelGroupLists = $this->ts3_VirtualServer->channelGroupClientList($job->channel_cgid,$channelListCID,$clDbID);
                //proof own channel is existing
                foreach ($ownChannelGroupLists as $ownChannelGrouplist)
                {
                    if($ownChannelGrouplist['cid'] == $channelListCID)
                    {
                        //channel is exist
                        $ownChannelExistBool = true;
                        //move user to channel
                        $this->ts3_VirtualServer->clientMove($clid, $channelListCID);

                        if ($goBackFlag == true)
                        {
                            //bot go back in standard channel
                            $this->ts3_VirtualServer->clientMove($this->self_clid, $this->standard_channel_id);
                        }
                    }
                }
            }

            //if client min count configured
            if($job->action_min_clients <= $clientsOnChannel->count() && $ownChannelExistBool == false)
            {
                //proof if channel name available
                $ifAvailable = false;
                $ifMaxChannelReached = false;
                $channelCount = 0;
                $channelDisplayCount = 1;
                $newChannelname = substr($chName,0,37).' ' .$channelCount;

                while($ifAvailable == false)
                {
                    //set channelname
                    $newChannelname = substr($chName,0,37).'-'.$channelDisplayCount;
                    $channelAvailable = $this->ts3_VirtualServer->channelList(array(
                        "channel_name" => $newChannelname,
                    ));
                    $channelAvailableCount = collect($channelAvailable)->count();

                    if ($channelAvailableCount == 0)
                    {
                        $ifAvailable = true;

                        //if create_max_channels == 0 then unlimited channels can be created
                        if ($channelCount >= $job->create_max_channels && $job->create_max_channels != 0)
                        {
                            $ifMaxChannelReached = true;
                        }

                    }else
                    {
                        $channelCount = $channelCount + 1;
                        $channelDisplayCount = $channelDisplayCount + 1;
                    }
                }

                //if Channel Template is set then copy the permissions
                if ($ifMaxChannelReached == false && ($job->channel_template_id != 0 || $job->channel_template_id != null) == true)
                {
                    //get channel permissions
                    $templateChannel = ts3Channel::query()->where('id','=',$job->channel_template_id)->first();

                    //create standard channel
                    $createdCID = $this->ts3_VirtualServer->channelCreate(array(
                        "channel_name" => $newChannelname,
                        "channel_codec" => $templateChannel->channel_codec,
                        "channel_codec_quality" => $templateChannel->channel_codec_quality,
                        "channel_flag_semi_permanent" => $boolPerm,
                        "channel_flag_permanent" => $boolSemi,
                        "channel_needed_talk_power" => $templateChannel->channel_needed_talk_power,
                        "channel_flag_maxclients_unlimited" => $templateChannel->channel_flag_maxclients_unlimited,
                        "channel_maxclients" => $templateChannel->channel_maxclients,
                        "channel_flag_maxfamilyclients_inherited" => $templateChannel->channel_flag_maxfamilyclients_inherited,
                        "channel_codec_is_unencrypted" => $templateChannel->channel_codec_is_unencrypted,
                        "cpid" => $job->on_cid,
                    ));

                    $templatePermission = $this->ts3_VirtualServer->channelGetById($templateChannel->cid);
                    $templatePermission = $templatePermission->permList();

                    //get created channel
                    $createdChannel = $this->ts3_VirtualServer->channelGetById($createdCID);

                    //set permissions
                    foreach ($templatePermission as $permission)
                    {
                        $createdChannel->permAssign($permission['permid'],$permission['permvalue']);
                    }
                }elseif ($ifMaxChannelReached == false)
                {
                    //create standard channel
                    $createdCID = $this->ts3_VirtualServer->channelCreate(array(
                        "channel_name" => $newChannelname,
                        "channel_codec" => 4,
                        "channel_codec_quality" => 6,
                        "channel_flag_semi_permanent" => $boolPerm,
                        "channel_flag_permanent"=>$boolSemi,
                        "cpid" => $job->on_cid,
                    ));
                }

                //move User in Created Channel
                if($job->rel_action_user->action_bot == 'client_move_to_created_channel' && $ifMaxChannelReached == false)
                {
                    //if client min count configured move all clients in created channel
                    if ($job->action_min_clients <= $clientsOnChannel->count() && $job->action_min_clients > 1)
                    {
                        foreach ($clientsOnChannel->keys()->all() as $clientID)
                        {
                            $this->ts3_VirtualServer->clientMove($clientID, $createdCID);
                        }
                    }else
                    {
                        //move client in created channel
                        $this->ts3_VirtualServer->clientMove($clid, $createdCID);
                    }

                    //if channel group id not 0 then set the Channel Group cgid
                    if ($job->channel_cgid != 0)
                    {
                        $this->ts3_VirtualServer->clientSetChannelGroup($clDbID,$createdCID,$job->channel_cgid);
                    }
                }

                //if channel temp then bot go back in standard channel
                if($goBackFlag == true)
                {
                    //bot go back in standard channel
                    $this->ts3_VirtualServer->clientMove($this->self_clid, $this->standard_channel_id);
                }

                //notify_message_server_group = true
                if($job->notify_message_server_group == 1 && $ifMaxChannelReached == false)
                {
                    $notifyClients = collect($this->ts3_VirtualServer->clientList(['client_servergroups'=>$job->notify_message_server_group_sgid]));
                    //build Message
                    $msg = str_replace(
                        ['{client-name}','{channel-name}'],
                        [$clName->toString(), $chName],
                        $job->notify_message_server_group_message
                    );

                    foreach ($notifyClients as $notifyClient)
                    {
                        $notifyUser = $this->ts3_VirtualServer->clientGetById($notifyClient['clid']);
                        $notifyUser->message($msg);
                    }
                }
            }
        }
        catch(TeamSpeak3Exception $e)
        {
            //set log
            $this->logController->setLog($e->getMessage(),4,'CreateChannel');
        }
    }

    private function storeCreatedChannel($cid): void
    {
        //check auto Update ist active
        $autoUpdateActive = ts3BotWorkerPolice::query()->where('server_id','=',$this->serverID)->first()->channel_auto_update;

        if ($autoUpdateActive == true)
        {
            try {
                //reset channel list
                $this->ts3_VirtualServer->channelListReset();
                //get channel by id
                $channel = $this->ts3_VirtualServer->channelGetById($cid);
                //get channel info
                $channelInfo = $channel->getInfo();
                //store info
                $ts3ConfigController = new Ts3ConfigController();
                $ts3ConfigController->createChannels($this->serverID,$channelInfo,$channel->toString());

            }catch (TeamSpeak3Exception $e)
            {
                //set log
                $this->logController->setLog($e->getMessage(),4,'storeCreatedChannel');
            }
        }
    }

    private function updateChannel($cid): void
    {
        //check auto Update ist active
        $autoUpdateActive = ts3BotWorkerPolice::query()->where('server_id','=',$this->serverID)->first()->channel_auto_update;

        if ($autoUpdateActive == true)
        {
            try {
                //reset channel list
                $this->ts3_VirtualServer->channelListReset();
                //get channel by id
                $channel = $this->ts3_VirtualServer->channelGetById($cid);
                //get channel info
                $channelInfo = $channel->getInfo();
                //store info
                $ts3ConfigController = new Ts3ConfigController();
                $ts3ConfigController->updateChannels($this->serverID,$channelInfo,$channel->toString(),$cid);

            }catch (TeamSpeak3Exception $e)
            {
                //set log
                $this->logController->setLog($e->getMessage(),4,'updateChannel');
            }
        }
    }

    private function deleteChannel($cid): void
    {
        ts3Channel::query()
            ->where('server_id','=',$this->serverID)
            ->where('cid','=',$cid)
            ->delete();

        ts3BotJobCreateChannels::query()
            ->where('server_id','=',$this->serverID)
            ->where('on_cid','=',$cid)
            ->delete();

        ts3BotWorkerChannelRemover::query()
            ->where('server_id','=',$this->serverID)
            ->where('channel_cid','=',$cid)
            ->delete();
    }

    private function reconnectBot(): int
    {
        //set custom log
        $this->logController->setCustomLog(
            $this->serverID,
            2,
            'reconnectBot',
            'Es wird versucht die Verbindung neu aufzubauen',
        );

        $waitTimeSeconds = 10 * $this->waitIncrease;

        sleep($waitTimeSeconds);

        $this->waitIncrease = $this->waitIncrease + 1;

        if ($this->waitIncrease >= 5)
        {
            //set custom log
            $this->logController->setCustomLog(
                $this->serverID,
                4,
                'reconnectBot',
                'Maximale Versuche und Wartezeit ('.$waitTimeSeconds.' sekunden) erreicht',
            );

            //bot stopped
            return ts3ServerConfig::BotReconnectFalse;
        }else
        {
            //set custom log
            $this->logController->setCustomLog(
                $this->serverID,
                2,
                'reconnectBot',
                'Neuer Verbindungsversuch in '.$waitTimeSeconds.' sekunden',
            );

            //bot start
            return ts3ServerConfig::BotReconnectTrue;
        }
    }
}
