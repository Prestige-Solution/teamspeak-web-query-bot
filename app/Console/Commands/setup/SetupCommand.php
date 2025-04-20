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
    protected $description = 'Setup application';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        //setup
        shell_exec('php artisan storage:link');
        shell_exec('php artisan migrate');
        shell_exec('php artisan migrate:status');
        shell_exec('php artisan db:seed');
        shell_exec('php artisan cache:clear');
        shell_exec('php artisan optimize');
        shell_exec('php artisan view:cache');

        return self::SUCCESS;
    }
}
