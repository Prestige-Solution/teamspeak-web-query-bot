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
        //setup
        Artisan::call('cache:clear');
        $this->info('Application cache cleared');
        Artisan::call('view:clear');
        $this->info('View cache cleared');
        Artisan::call('route:clear');
        $this->info('Route cache cleared');
        Artisan::call('cache:clear');
        $this->info('Route cache cleared');

        Artisan::call('storage:link');
        $this->info('Storage linked');
        Artisan::call('migrate', ['--force' => true]);
        $this->info('Migrated');
        Artisan::call('db:seed', ['--force' => true]);
        $this->info('Seeded');

        Artisan::call('optimize');
        $this->info('Optimized');
        Artisan::call('view:cache');
        $this->info('View cached');

        return self::SUCCESS;
    }
}
