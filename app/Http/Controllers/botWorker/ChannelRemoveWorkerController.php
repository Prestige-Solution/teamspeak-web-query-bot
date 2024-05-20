<?php

namespace App\Http\Controllers\botWorker;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sys\Ts3LogController;
use App\Models\ts3Bot\ts3ServerConfig;
use App\Models\ts3BotWorkers\ts3BotWorkerChannelRemover;
use Illuminate\Support\Facades\Crypt;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TeamSpeak3Exception;

class ChannelRemoveWorkerController extends Controller
{
    protected int $serverID;
    protected Ts3LogController $logController;

    public function startChannelRemover($serverID): void
    {
        //declare
        $this->serverID = $serverID;
        $this->logController = new Ts3LogController('ChannelRemoverWorker', $this->serverID);

        try {
            //get Server config
            $ts3ServerConfig = ts3ServerConfig::query()
                ->where('id', '=', $serverID)->first();

            if ($ts3ServerConfig->qa_nickname != null)
            {
                $qaNickname = $ts3ServerConfig->qa_nickname;
            } else
            {
                $qaNickname = $ts3ServerConfig->qa_name;
            }

            //declare class
            $TS3PHPFramework = new TeamSpeak3();

            // Connect via ipv4 to ts3 Server // timeout in seconds
            $uri = 'serverquery://'
                .$ts3ServerConfig->qa_name.':'.Crypt::decryptString($ts3ServerConfig->qa_pw).
                '@'.$ts3ServerConfig->ipv4.
                ':'.$ts3ServerConfig->server_query_port.
                '/?server_port='.$ts3ServerConfig->server_port.
                '&blocking=0'.
                '&no_query_clients'.
                '&nickname='.$qaNickname.'-Remove-Worker';

            // connect to the server
            $ts3_VirtualServer = $TS3PHPFramework->factory($uri);

            //get sub-channels
            $subChannelRemoves = ts3BotWorkerChannelRemover::query()
                ->where('server_id','=',$this->serverID)
                ->where('active','=',1)
                ->get();

            foreach ($subChannelRemoves as $subChannelRemove)
            {
                //get sub-channel list
                $subChannels = collect($ts3_VirtualServer->channelList(['pid'=>$subChannelRemove->channel_cid]));

                //proof delete time
                foreach ($subChannels->keys()->all() as $subChannel)
                {
                    $subChannelInfo = $ts3_VirtualServer->channelGetById($subChannel)->getInfo();

                    if($subChannelInfo['seconds_empty'] >= $subChannelRemove->channel_max_seconds_empty)
                    {
                        $ts3_VirtualServer->channelDelete($subChannel);
                    }
                }
            }
        }
        catch(TeamSpeak3Exception $e)
        {
            //set log
            $this->logController->setLog($e,4,'startChannelRemover');
        }
    }
}
