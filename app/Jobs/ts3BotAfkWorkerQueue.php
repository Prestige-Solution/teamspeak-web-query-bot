<?php

namespace App\Jobs;

use App\Http\Controllers\botWorker\AfkWorkerController;
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

class ts3BotAfkWorkerQueue implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

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
        return [(new WithoutOverlapping($this->server_id))->dontRelease()];
    }

    /**
     * Execute the job.
     *
     * @throws Exception
     */
    public function handle(): void
    {
        try {
            $afkWorker = new AfkWorkerController($this->server_id);
            $afkWorker->afkMoverWorker();
        } catch (Exception $e) {
            $ts3Logging = new Ts3LogController('Afk-Worker', $this->server_id);
            $ts3Logging->setCustomLog($this->server_id, ts3BotLog::FAILED,'queue_worker', $e->getMessage());
        }
    }

    public function uniqueId(): int
    {
        return $this->server_id;
    }
}
