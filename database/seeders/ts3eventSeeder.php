<?php

namespace Database\Seeders;

use App\Models\ts3BotEvents\ts3BotEvent;
use Illuminate\Database\Seeder;

class ts3eventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ts3BotEvent::query()->create([
            'event_ts'=>'clientmoved',
            'event_name'=>'Client betritt Channel',
            'event_description'=>'Ein Client bewegt sich auf dem Server',
            'cat_job_type'=>2,
        ]);
        ts3BotEvent::query()->create([
            'event_ts'=>'cliententerview',
            'event_name'=>'Client betritt den Server',
            'event_description'=>'Client betritt den Teamspeak Server',
            'cat_job_type'=>3,
        ]);
        ts3BotEvent::query()->create([
            'event_ts'=>'clientleftview',
            'event_name'=>'Client verlässt den Server',
            'event_description'=>'Client verlässt den Teamspeak Server',
            'cat_job_type'=>3,
        ]);
    }
}