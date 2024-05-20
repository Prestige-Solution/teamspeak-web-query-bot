<?php

namespace App\Console\Commands\setup;

use Illuminate\Console\Command;

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
    protected $description = 'Setup Initial Server Configuration';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {

        $appURL = $this->ask('Enter your URL Address');
        $dbHost = $this->ask('Enter your Database Connection (URL or IP)');
        $dbName = $this->ask('Enter Database Name (Not the Username)');
        $dbUsername = $this->ask('Enter Database Username');
        $dbPassword = $this->secret('Enter Database Password');

        $path = base_path('.env');

        if (file_exists($path))
        {
            file_put_contents($path, str_replace(
                'APP_URL='.$this->laravel['config']['url'],'APP_URL='.$appURL,file_get_contents($path)
            ));

            file_put_contents($path, str_replace(
                'DB_HOST='.config('database.connections.mysql.host'),'DB_HOST='.$dbHost,file_get_contents($path)
            ));

            file_put_contents($path, str_replace(
                'DB_DATABASE='.config('database.connections.mysql.database'),'DB_DATABASE='.$dbName,file_get_contents($path)
            ));

            file_put_contents($path, str_replace(
                'DB_USERNAME='.config('database.connections.mysql.username'),'DB_USERNAME='.$dbUsername,file_get_contents($path)
            ));

            file_put_contents($path, str_replace(
                'DB_PASSWORD='.config('database.connections.mysql.password'),'DB_PASSWORD='.$dbPassword,file_get_contents($path)
            ));
        }

        $this->info('Server Configuration Setup Completed');

        return self::SUCCESS;
    }
}
