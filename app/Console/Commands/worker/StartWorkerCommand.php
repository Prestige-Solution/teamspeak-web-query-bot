<?php

namespace App\Console\Commands\worker;

use App\Jobs\tsBannerWorkerQueue;
use App\Jobs\tsBotAfkWorkerQueue;
use App\Jobs\tsBotChannelRemoveWorkerQueue;
use App\Jobs\tsBotPoliceWorkerQueue;
use App\Models\tsBot\tsServerConfig;
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
        $servers = tsServerConfig::query()
            ->where('is_ts_start', '=', true)
            ->where('is_active', '=', true)
            ->get();

        foreach ($servers as $server) {
            try {
                tsBannerWorkerQueue::dispatch($server->id)->onConnection('worker')->onQueue('bannerWorker');
                tsBotAfkWorkerQueue::dispatch($server->id)->onConnection('worker')->onQueue('afkWorker');
                tsBotChannelRemoveWorkerQueue::dispatch($server->id)->onConnection('worker')->onQueue('channelRemoverWorker');
                tsBotPoliceWorkerQueue::dispatch($server->id)->onConnection('worker')->onQueue('policeWorker');
            } catch (Exception $e) {
                Log::channel('queueWorker')->error($e);
            }
        }
    }
}
