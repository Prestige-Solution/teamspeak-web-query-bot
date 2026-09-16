<?php

namespace Database\Seeders;

use App\Models\tsBotEvents\tsBotActionUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class tsActionUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        tsBotActionUser::query()->create([
            'action_bot'=>'0',
            'action_name'=>'No Action',
            'type_id'=>1,
        ]);
        tsBotActionUser::query()->create([
            'action_bot'=>'client_move_to_created_channel',
            'action_name'=>'Move client to channel',
            'type_id'=>1,
        ]);
    }
}
