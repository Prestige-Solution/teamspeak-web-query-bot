<?php

namespace Database\Seeders;

use App\Models\ts3BotEvents\ts3BotActionUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ts3ActionUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        ts3BotActionUser::query()->create([
            'action_bot'=>'0',
            'action_name'=>'No Action',
            'type_id'=>1,
        ]);
        ts3BotActionUser::query()->create([
            'action_bot'=>'client_move_to_created_channel',
            'action_name'=>'Move client to channel',
            'type_id'=>1,
        ]);
    }
}
