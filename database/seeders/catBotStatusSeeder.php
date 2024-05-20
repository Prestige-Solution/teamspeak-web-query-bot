<?php

namespace Database\Seeders;

use App\Models\category\catBotStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class catBotStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        catBotStatus::query()->create([
            'status_name'=>'Running',
        ]);
        catBotStatus::query()->create([
            'status_name'=>'Trying to (re)connect',
        ]);
        catBotStatus::query()->create([
            'status_name'=>'Stopped',
        ]);
        catBotStatus::query()->create([
            'status_name'=>'Failed',
        ]);
        catBotStatus::query()->create([
            'status_name'=>'Success',
        ]);
    }
}
