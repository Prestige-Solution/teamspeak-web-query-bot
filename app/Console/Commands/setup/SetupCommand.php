<?php

namespace App\Console\Commands\setup;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup initial setup';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $choiceGenerateAppKey = $this->choice('Generate app key?', ['yes', 'no'], 1);
        $choiceDbCon = $this->choice('Choose your database', ['mysql', 'pgsql'], 0);
        $appURL = $this->ask('Enter your URL address');
        $dbHost = $this->ask('Enter your database connection');
        $dbName = $this->ask('Enter your database name');
        $dbUsername = $this->ask('Enter your database user');
        $dbPassword = $this->secret('Enter your database user password');

        $dbCon = match ($choiceDbCon) {
            'pgsql' => 'pgsql',
            default => 'mysql',
        };

        if ($choiceGenerateAppKey === 'yes') {
            Artisan::call('key:generate');
        }

        $path = base_path('.env');

        if (file_exists($path)) {
            file_put_contents($path, str_replace(
                'APP_URL=', 'APP_URL='.$appURL, file_get_contents($path)
            ));

            file_put_contents($path, str_replace(
                'DB_CONNECTION=', 'DB_CONNECTION='.$dbCon, file_get_contents($path)
            ));

            file_put_contents($path, str_replace(
                'DB_HOST=', 'DB_HOST='.$dbHost, file_get_contents($path)
            ));

            file_put_contents($path, str_replace(
                'DB_DATABASE=', 'DB_DATABASE='.$dbName, file_get_contents($path)
            ));

            file_put_contents($path, str_replace(
                'DB_USERNAME=', 'DB_USERNAME='.$dbUsername, file_get_contents($path)
            ));

            file_put_contents($path, str_replace(
                'DB_PASSWORD=', 'DB_PASSWORD='.$dbPassword, file_get_contents($path)
            ));
        }

        $this->info('Setup finished');

        return self::SUCCESS;
    }
}
