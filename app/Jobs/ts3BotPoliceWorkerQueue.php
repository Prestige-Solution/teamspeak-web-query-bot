<?php

namespace App\Jobs;

use App\Http\Controllers\botWorker\PoliceWorkerController;
use App\Http\Controllers\sys\Ts3LogController;
use App\Models\ts3Bot\ts3BotLog;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class ts3BotPoliceWorkerQueue implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $backoff = 60;

    public int $server_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($server_id)
    {
        $this->server_id = $server_id;
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->server_id))->expireAfter(180)];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $worker = new PoliceWorkerController($this->server_id);
            $worker->startPolice();
        } catch (Exception $e) {
            $ts3Logging = new Ts3LogController('Police-Worker', $this->server_id);
            $ts3Logging->setCustomLog(
                $this->server_id,
                ts3BotLog::SUCCESS,
                'queue_worker',
                'There was an error during create queue',
                $e->getCode(),
                $e->getMessage()
            );
        }
    }

    public function uniqueId(): int
    {
        return $this->server_id;
    }

    public function backoff(): int
    {
        return $this->backoff;
    }
}
