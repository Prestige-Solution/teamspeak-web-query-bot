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
        $outputConfigClear = shell_exec('php artisan config:clear');
        $outputViewClear = shell_exec('php artisan view:clear');
        $outputRouteClear = shell_exec('php artisan route:clear');
        $outputCacheClear = shell_exec('php artisan cache:clear');
        $this->info(trim($outputConfigClear));
        $this->info(trim($outputViewClear));
        $this->info(trim($outputCacheClear));
        $this->info(trim($outputRouteClear));

        $outputStorage = shell_exec('php artisan storage:link');
        $outputMigrate = shell_exec('php artisan migrate');
        $outputMigrateStatus = shell_exec('php artisan migrate:status');
        $outputSeeder = shell_exec('php artisan db:seed');
        $this->info(trim($outputStorage));
        $this->info(trim($outputMigrate));
        $this->info(trim($outputMigrateStatus));
        $this->info(trim($outputSeeder));

        $outputOptimize = shell_exec('php artisan optimize');
        $outputViewCache = shell_exec('php artisan view:cache');
        $this->info(trim($outputOptimize));
        $this->info(trim($outputViewCache));

        return self::SUCCESS;
    }
}
