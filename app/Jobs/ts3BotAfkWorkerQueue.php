<?php

namespace App\Jobs;

use App\Http\Controllers\botWorker\AfkWorkerController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ts3BotAfkWorkerQueue implements ShouldQueue, ShouldBeUnique
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
        //declare class
        $afkWorker = new AfkWorkerController();
        $afkWorker->afkMoverWorker($this->serverID);
    }

    public function uniqueId(): string
    {
        return $this->serverID;
    }
}
