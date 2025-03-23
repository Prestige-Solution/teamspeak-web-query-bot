<?php

namespace App\Jobs;

use App\Http\Controllers\botWorker\ClearingWorkerController;
use App\Http\Controllers\sys\Ts3LogController;
use App\Models\ts3Bot\ts3BotLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ts3ClearingWorkerQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $server_id;

    public int $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct($server_id)
    {
        $this->server_id = $server_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $clearingController = new ClearingWorkerController($this->server_id);
            $clearingController->startClearing();
        } catch (\Exception $exception) {
            $ts3Logging = new Ts3LogController('Clearing-Worker', $this->serverID);
            $ts3Logging->setLog($exception->getMessage(), ts3BotLog::SUCCESS, 'Start Queue Clearing-Worker failed');
        }
    }

    public function uniqueId(): string
    {
        return $this->serverID;
    }
}
