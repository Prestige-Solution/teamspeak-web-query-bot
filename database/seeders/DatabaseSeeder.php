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
        //get seed_version
        $seedVersion = $this->getCurrentSeedVersion();
        $this->command->info('[CURRENT SEED VERSION = '.$seedVersion.' ]');

        if ($seedVersion <= 0) {
            //start seeding
            $this->command->info('[Start SEED NEW VERSION = '.$seedVersion + 1 .']');

            $botStatusSeeder = new catBotStatusSeeder();
            $botStatusSeeder->run();

            $botJobTypeSeeder = new catBotJobTypeSeeder();
            $botJobTypeSeeder->run();

            $BotActionSeeder = new ts3ActionSeeder();
            $BotActionSeeder->run();

            $botActionuserSeeder = new ts3ActionUserSeeder();
            $botActionuserSeeder->run();

            $botEventSeeder = new ts3eventSeeder();
            $botEventSeeder->run();

            $catFonts = new catFontSeeder();
            $catFonts->run();

            $catBannerOptionSeeder = new catBannerOptionSeeder();
            $catBannerOptionSeeder->run();

            $sysBadName = new sysBadNameSeeder();
            $sysBadName->run();

            if (config('app.env') != 'production') {
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
            }

            //finish seed
            $this->command->info('[SEED VERSION '.$seedVersion + 1 .' Finished]');
            $this->setNewSeedVersion($seedVersion);
        }

        //finish seed
        $this->command->info('[SEEDING] Finished');
    }

    private function getCurrentSeedVersion()
    {
        return system_config::query()->first(['seed_version'])->seed_version;
    }

    private function setNewSeedVersion($currentSeedVersion): void
    {
        $newSeedVersion = $currentSeedVersion + 1;
        system_config::query()->update(['seed_version'=>$newSeedVersion]);
    }
}
