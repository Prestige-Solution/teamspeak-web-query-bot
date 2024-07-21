<?php

namespace App\Console\Commands\worker;

use App\Jobs\ts3ClearingWorkerQueue;
use App\Models\ts3Bot\ts3ServerConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        //get all active servers
        $activeServerIds = ts3ServerConfig::query()
            ->where('bot_confirmed','=',true)
            ->where('ts3_start_stop','=',true)
            ->where('active','=',true)
            ->get(['id']);

        foreach ($activeServerIds as $activeServerId)
        {
            Bus::chain([
                new ts3ClearingWorkerQueue($activeServerId->id)
            ])
                ->catch(function (Throwable $e)
                {
                    Log::channel('busChain')->error($e);
                })
                ->onConnection('worker')
                ->onQueue('clearing')
                ->dispatch();
        }
    }
}
