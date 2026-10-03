<?php

namespace Database\Seeders;

use App\Models\tsBotEvents\tsBotEvent;
use Illuminate\Database\Seeder;

class tsEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        tsBotEvent::query()->create([
            'event_ts'=>'clientmoved',
            'event_name'=>'Client enters channel',
            'event_description'=>'A client moves on the server',
            'cat_job_type'=>2,
        ]);
        tsBotEvent::query()->create([
            'event_ts'=>'cliententerview',
            'event_name'=>'Client enters the server',
            'event_description'=>'Client enters the Teamspeak server',
            'cat_job_type'=>3,
        ]);
        tsBotEvent::query()->create([
            'event_ts'=>'clientleftview',
            'event_name'=>'Client leaves the server',
            'event_description'=>'Client leaves the Teamspeak server',
            'cat_job_type'=>3,
        ]);
    }
}
