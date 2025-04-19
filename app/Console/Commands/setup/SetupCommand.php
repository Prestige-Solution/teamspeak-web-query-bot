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
    protected $description = 'Setup application';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Artisan::call('storage:link');
        Artisan::call('migrate');
        Artisan::call('migrate:status');
        Artisan::call('db:seed');
        Artisan::call('cache:clear');
        Artisan::call('optimize');
        Artisan::call('view:cache');

        return self::SUCCESS;
    }
}
