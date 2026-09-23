<?php

namespace App\Http\Controllers\sys;

use App\Http\Controllers\Controller;
use App\Models\sys\statistic;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\AdapterException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\NodeException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TransportException;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;

class StatisticController extends Controller
{
    /**
     * @throws AdapterException
     * @throws TransportException
     * @throws NodeException
     * @throws ServerQueryException
     */
    public function gatherVirtualServerStatistic(int $serverId, Server $tsVirtualServer): void
    {
        //update virtual server statistic
        $stats = $tsVirtualServer->getInfo(true, true);
        $serverGroupsCount = count($tsVirtualServer->serverGroupList(['type' => 1]));
        $serverChannelGroupsCount = count($tsVirtualServer->channelGroupList(['type' => 1]));
        $banListCount = $tsVirtualServer->banCount();

        statistic::query()->updateOrCreate(
            [
                'server_id' => $serverId,
            ],
            [
                'virtualserver_server_group_count' => $serverGroupsCount,
                'virtualserver_channel_group_count' => $serverChannelGroupsCount,
                'virtualserver_banlist_count' => $banListCount,
                'virtualserver_clientsonline' => $stats['virtualserver_clientsonline'],
                'virtualserver_queryclientsonline' => $stats['virtualserver_queryclientsonline'],
                'virtualserver_maxclients' => $stats['virtualserver_maxclients'],
                'virtualserver_channelsonline' => $stats['virtualserver_channelsonline'],
                'virtualserver_platform' => $stats['virtualserver_platform'],
                'virtualserver_version' => $stats['virtualserver_version']->toString(),
                'virtualserver_uptime' => $stats['virtualserver_uptime'],
                'virtualserver_total_packetloss_keepalive' => $stats['virtualserver_total_packetloss_keepalive'],
                'virtualserver_total_ping' => $stats['virtualserver_total_ping'],
                'virtualserver_connection_bytes_received_keepalive' => $stats['connection_bytes_received_keepalive'],
                'virtualserver_connection_bytes_sent_keepalive' => $stats['connection_bytes_sent_keepalive'],
                'virtualserver_total_packetloss_speech' => $stats['virtualserver_total_packetloss_speech'],
                'virtualserver_reserved_slots' => $stats['virtualserver_reserved_slots'],
            ]);
    }

    public function deleteStatisticsByServerId(int $serverId): void
    {
        statistic::query()->where('server_id', '=', $serverId)->delete();
    }
}
