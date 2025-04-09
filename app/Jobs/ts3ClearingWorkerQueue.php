<?php

namespace App\Jobs;

use App\Http\Controllers\botWorker\ClearingWorkerController;
use App\Http\Controllers\sys\Ts3LogController;
use App\Models\ts3Bot\ts3BotLog;
use Exception;
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
        } catch (Exception $e) {
            $ts3Logging = new Ts3LogController('Clearing-Worker', $this->server_id);
            $ts3Logging->setCustomLog(
                $this->server_id,
                ts3BotLog::FAILED,
                'startQueue',
                $e->getMessage(),
            );
        }
    }

    public function uniqueId(): string
    {
        return $this->server_id;
    }
}
