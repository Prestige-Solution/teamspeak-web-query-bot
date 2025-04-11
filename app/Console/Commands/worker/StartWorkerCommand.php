<?php

namespace App\Console\Commands\worker;

use App\Jobs\ts3BannerWorkerQueue;
use App\Jobs\ts3BotAfkWorkerQueue;
use App\Jobs\ts3BotChannelRemoveWorkerQueue;
use App\Jobs\ts3BotPoliceWorkerQueue;
use App\Models\ts3Bot\ts3ServerConfig;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class StartWorkerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:start-worker';

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
        $servers = ts3ServerConfig::query()
            ->where('is_ts3_start', '=', true)
            ->where('is_active', '=', true)
            ->get();

        foreach ($servers as $server) {
            Bus::chain([
                new ts3BannerWorkerQueue($server->id),
                new ts3BotAfkWorkerQueue($server->id),
                new ts3BotChannelRemoveWorkerQueue($server->id),
                new ts3BotPoliceWorkerQueue($server->id),
            ])
            ->catch(function (Exception $e) {
                Log::channel('busChain')->error($e);
            })
            ->onConnection('worker')
            ->onQueue('worker')
            ->dispatch();
        }
    }
}
