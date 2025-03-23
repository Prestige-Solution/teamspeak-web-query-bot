<?php

namespace App\Jobs;

use App\Http\Controllers\botWorker\PoliceWorkerController;
use App\Http\Controllers\sys\Ts3LogController;
use App\Models\ts3Bot\ts3BotLog;
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
     */
    public function handle(): void
    {
        try {
            $worker = new PoliceWorkerController($this->server_id);
            $worker->startPolice();
        } catch (\Exception $exception) {
            $ts3Logging = new Ts3LogController('Police-Worker', $this->serverID);
            $ts3Logging->setLog($exception->getMessage(), ts3BotLog::SUCCESS, 'Start Queue Police-Worker failed');
        }
    }

    public function uniqueId(): string
    {
        return $this->serverID;
    }
}
