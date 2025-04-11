<?php

namespace App\Console\Commands\worker;

use App\Jobs\ts3ClearingWorkerQueue;
use App\Models\ts3Bot\ts3ServerConfig;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
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
        $servers = ts3ServerConfig::query()
            ->where('is_ts3_start', '=', true)
            ->where('is_active', '=', true)
            ->get(['id']);

        foreach ($servers as $server) {
            Bus::chain([
                new ts3ClearingWorkerQueue($server->id),
            ])
            ->catch(function (Exception $e) {
                Log::channel('busChain')->error($e);
            })
            ->onConnection('worker')
            ->onQueue('clearing')
            ->dispatch();
        }
    }
}
