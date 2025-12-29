<?php

namespace Database\Seeders;

use App\Models\sys\statistic;
use App\Models\ts3Bot\ts3ServerConfig;
use Illuminate\Database\Seeder;

class defaultStatsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //get all configured servers
        $servers = ts3ServerConfig::query()->get();

        //set default stats for each server
        foreach($servers as $server){
            statistic::query()->updateOrCreate(
                [
                    'server_id' => $server->id,
                ],
                [
                'virtualserver_clientsonline' => 0,
                'virtualserver_queryclientsonline'=> 0,
                'virtualserver_maxclients'=> 0,
                'virtualserver_channelsonline' => 0,
                'virtualserver_platform' => 'default',
                'virtualserver_version' => 'default',
                'virtualserver_uptime' => 'default',
                'virtualserver_total_packetloss_keepalive' => '0.00%',
                'virtualserver_total_ping' => 0,
                'virtualserver_connection_bytes_received_keepalive' => 'default',
                'virtualserver_connection_bytes_sent_keepalive' => 'default',
                'virtualserver_total_packetloss_speech'=>'0.00%',
            ]);
        }
    }
}
