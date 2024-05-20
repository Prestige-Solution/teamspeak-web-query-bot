<?php

namespace Database\Seeders;

use App\Models\category\catBotJobType;
use Illuminate\Database\Seeder;

class catBotJobTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        catBotJobType::query()->create([
            'type_name'=>'channel',
        ]);
        catBotJobType::query()->create([
            'type_name'=>'client',
        ]);
        catBotJobType::query()->create([
            'type_name'=>'server',
        ]);
    }
}
