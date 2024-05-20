<?php

namespace App\Jobs;

use App\Http\Controllers\botWorker\BannerWorkerController;
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
    protected int $serverID;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($serverID)
    {
        $this->serverID = $serverID;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle(): void
    {
        //declare Controller
        $bannerWorker = new BannerWorkerController();
        $bannerWorker->bannerWorkerCreateBanner($this->serverID);
    }

    public function uniqueId(): string
    {
        return $this->serverID;
    }
}
