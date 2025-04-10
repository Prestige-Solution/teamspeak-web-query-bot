<?php

namespace App\Jobs;

use App\Http\Controllers\botWorker\BannerWorkerController;
use App\Http\Controllers\sys\Ts3LogController;
use App\Models\ts3Bot\ts3BotLog;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ts3BannerWorkerQueue implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    protected int $server_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($server_id)
    {
        $this->server_id = $server_id;
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle(): void
    {
        try {
            $bannerWorker = new BannerWorkerController();
            $bannerWorker->bannerWorkerCreateBanner($this->server_id);
        }catch (Exception $e)
        {
            $ts3Logging = new Ts3LogController('Afk-Worker', $this->server_id);
            $ts3Logging->setCustomLog($this->server_id, ts3BotLog::FAILED,'queue_worker', $e->getMessage());
        }
    }

    public function uniqueId(): string
    {
        return $this->server_id;
    }
}
