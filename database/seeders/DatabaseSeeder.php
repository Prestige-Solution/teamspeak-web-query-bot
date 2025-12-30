<?php

namespace Database\Seeders;

use App\Models\sys\system_config;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        if (config('app.env') != 'production') {
            $this->command->info('[SEEDING] Start create Testuser');
            User::query()->create([
                'nickname'=>'Testuser',
                'email'=>'test@test.de',
                'password'=>'test',
            ]);

            User::query()->create([
                'nickname'=>'Testuser2',
                'email'=>'test2@test.de',
                'password'=>'test',
            ]);

            $this->command->info('[SEEDING] Testuser finished');
        }
    }
}
