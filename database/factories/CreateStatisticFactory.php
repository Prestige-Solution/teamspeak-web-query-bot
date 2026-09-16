<?php

namespace Database\Factories;

use App\Models\sys\statistic;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreateStatisticFactory extends Factory
{

    protected $model = statistic::class;
    public function definition(): array
    {
        return [
            'server_id' => 1,
            'virtualserver_server_group_count' => 1,
            'virtualserver_channel_group_count' => 1,
            'virtualserver_banlist_count' => 1,
            'virtualserver_clientsonline' => 1,
            'virtualserver_queryclientsonline' => 1,
            'virtualserver_maxclients' => 20,
            'virtualserver_channelsonline' => 10,
            'virtualserver_platform' => 'Linux',
            'virtualserver_version' => '3.13.8 (2026-05-27 11:34:31)',
            'virtualserver_uptime' => '5D 23:37:17',
            'virtualserver_total_packetloss_keepalive' => '157.43 KiB',
            'virtualserver_total_ping' => 1,
            'virtualserver_connection_bytes_received_keepalive' => '161.40 KiB',
            'virtualserver_connection_bytes_sent_keepalive' => '157.43 KiB',
            'virtualserver_total_packetloss_speech' => '0.00%',
            'virtualserver_reserved_slots' => 1,
        ];
    }
}
