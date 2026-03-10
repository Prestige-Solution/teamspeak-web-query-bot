<?php

namespace Database\Seeders;

use App\Models\ts3BotEvents\ts3BotAction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ts3ActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        ts3BotAction::query()->create([
            'type_id'=>1,
            'action_bot'=>'create_channel_temp',
            'action_name'=>'Create temporary channel',
        ]);
        ts3BotAction::query()->create([
            'type_id'=>1,
            'action_bot'=>'create_channel_semi',
            'action_name'=>'Create semi-temporary channel',
        ]);
        ts3BotAction::query()->create([
            'type_id'=>1,
            'action_bot'=>'create_channel_perm',
            'action_name'=>'Create permanent channel',
        ]);
        ts3BotAction::query()->create([
            'type_id'=>3,
            'action_bot'=>'proof_bad_name',
            'action_name'=>'Check unwanted nicknames',
        ]);
    }
}
