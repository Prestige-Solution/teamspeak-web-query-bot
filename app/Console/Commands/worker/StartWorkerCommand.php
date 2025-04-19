<?php

namespace App\Console\Commands\worker;

use App\Jobs\ts3BannerWorkerQueue;
use App\Jobs\ts3BotAfkWorkerQueue;
use App\Jobs\ts3BotChannelRemoveWorkerQueue;
use App\Jobs\ts3BotPoliceWorkerQueue;
use App\Models\ts3Bot\ts3ServerConfig;
use Exception;
use Illuminate\Console\Command;
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
    protected $description = 'Start worker jobs';

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
            try {
                ts3BannerWorkerQueue::dispatch($server->id)->onConnection('worker')->onQueue('bannerWorker');
                ts3BotAfkWorkerQueue::dispatch($server->id)->onConnection('worker')->onQueue('afkWorker');
                ts3BotChannelRemoveWorkerQueue::dispatch($server->id)->onConnection('worker')->onQueue('channelRemoverWorker');
                ts3BotPoliceWorkerQueue::dispatch($server->id)->onConnection('worker')->onQueue('policeWorker');

            }catch (Exception $e) {
                Log::channel('queueWorker')->error($e);
            }
        }
    }
}
