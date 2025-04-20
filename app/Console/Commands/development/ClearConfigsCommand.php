<?php

namespace App\Console\Commands\development;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ClearConfigsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:clear-conf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'clear configs and caches';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Artisan::call('view:clear');
        $this->info('View cleared');
        Artisan::call('cache:clear');
        $this->info('Cache cleared');
        Artisan::call('route:clear');
        $this->info('Route cleared');
        Artisan::call('config:clear');
        $this->info('Configs cleared');
    }
}
