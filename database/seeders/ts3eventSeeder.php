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
    public function run(): void
    {
        ts3BotEvent::query()->create([
            'event_ts'=>'clientmoved',
            'event_name'=>'Client enters channel',
            'event_description'=>'A client moves on the server',
            'cat_job_type'=>2,
        ]);
        ts3BotEvent::query()->create([
            'event_ts'=>'cliententerview',
            'event_name'=>'Client enters the server',
            'event_description'=>'Client enters the Teamspeak server',
            'cat_job_type'=>3,
        ]);
        ts3BotEvent::query()->create([
            'event_ts'=>'clientleftview',
            'event_name'=>'Client leaves the server',
            'event_description'=>'Client leaves the Teamspeak server',
            'cat_job_type'=>3,
        ]);
    }
}
