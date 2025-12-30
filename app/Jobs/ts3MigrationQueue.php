<?php

namespace App\Jobs;

use App\Http\Controllers\botWorker\BannerWorkerController;
use App\Http\Controllers\botWorker\MigrationController;
use App\Http\Controllers\sys\Ts3LogController;
use App\Models\ts3Bot\ts3BotLog;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class ts3MigrationQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, \Illuminate\Bus\Queueable, SerializesModels;

    public int $tries = 1;

    public int $backoff = 60;

    public int $source_server_id;

    public int $target_server_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($source_server_id, $target_server_id)
    {
        $this->source_server_id = $source_server_id;
        $this->target_server_id = $target_server_id;
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->source_server_id))->expireAfter(180)];
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle(): void
    {
        try {
            $job = new MigrationController($this->source_server_id, $this->target_server_id);
            $job->setup_connections();
        } catch (Exception $e) {
            $ts3Logging = new Ts3LogController('Migration Job', $this->source_server_id);
            $ts3Logging->setCustomLog(
                $this->server_id,
                ts3BotLog::FAILED,
                'queue_worker',
                'There was an error during create migration queue',
                $e->getCode(),
                $e->getMessage()
            );
        }
    }

    public function uniqueId(): int
    {
        return $this->source_server_id;
    }

    public function backoff(): int
    {
        return $this->backoff;
    }
}
