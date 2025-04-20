<?php

namespace App\Console\Commands\development;

use Illuminate\Console\Command;

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
        $outputConfigClear = shell_exec('php artisan config:clear');
        $outputViewClear = shell_exec('php artisan view:clear');
        $outputRouteClear = shell_exec('php artisan route:clear');
        $outputCacheClear = shell_exec('php artisan cache:clear');
        $this->info(trim($outputConfigClear));
        $this->info(trim($outputViewClear));
        $this->info(trim($outputCacheClear));
        $this->info(trim($outputRouteClear));
    }
}
