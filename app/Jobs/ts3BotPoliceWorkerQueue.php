<?php

namespace App\Jobs;

use App\Http\Controllers\botWorker\PoliceWorkerController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ts3BotPoliceWorkerQueue implements ShouldQueue, ShouldBeUnique
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
     */
    public function handle()
    {
        //get all servers with active police worker
        //declare Controller
        $worker = new PoliceWorkerController();
        $worker->startPolice($this->serverID);
    }

    public function uniqueId(): string
    {
        return $this->serverID;
    }
}
