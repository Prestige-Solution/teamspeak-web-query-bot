<?php

namespace App\Console\Commands\worker;

use App\Jobs\tsClearingWorkerQueue;
use App\Models\tsBot\tsServerConfig;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class StartClearingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:start-clearing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start clearing jobs';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $servers = tsServerConfig::query()
            ->where('is_ts_start', '=', true)
            ->where('is_active', '=', true)
            ->get(['id']);

        foreach ($servers as $server) {
            try {
                tsClearingWorkerQueue::dispatch($server->id)->onConnection('worker')->onQueue('clearing');
            } catch (Exception $e) {
                Log::channel('queueWorker')->error($e);
            }
        }
    }
}
