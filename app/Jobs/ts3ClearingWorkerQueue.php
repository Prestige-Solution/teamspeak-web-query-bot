<?php

namespace App\Jobs;

use App\Http\Controllers\botWorker\ClearingWorkerController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ts3ClearingWorkerQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $serverID;

    public int $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct($serverID)
    {
        $this->serverID = $serverID;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //start clearing
        $clearingController = new ClearingWorkerController();
        $clearingController->startClearing($this->serverID);
    }

    public function uniqueId(): string
    {
        return $this->serverID;
    }
}
