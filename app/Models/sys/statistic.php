<?php

namespace App\Models\sys;

use Illuminate\Database\Eloquent\Model;

class statistic extends Model
{
    protected $fillable = [
        'server_id',
        'virtualserver_server_group_count',
        'virtualserver_channel_group_count',
        'virtualserver_banlist_count',
        'virtualserver_clientsonline',
        'virtualserver_queryclientsonline',
        'virtualserver_maxclients',
        'virtualserver_channelsonline',
        'virtualserver_platform',
        'virtualserver_version',
        'virtualserver_uptime',
        'virtualserver_total_packetloss_keepalive',
        'virtualserver_total_ping',
        'virtualserver_connection_bytes_received_keepalive',
        'virtualserver_connection_bytes_sent_keepalive',
        'virtualserver_total_packetloss_speech',
    ];
}
