<?php

namespace App\Jobs;

use App\Http\Controllers\botWorker\ChannelRemoverWorkerController;
use App\Http\Controllers\sys\TsLogController;
use App\Models\tsBot\tsBotLog;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class tsBotChannelRemoveWorkerQueue implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $backoff = 60;

    public int $serverId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($serverId)
    {
        $this->serverId = $serverId;
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->serverId))->expireAfter(180)];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $worker = new ChannelRemoverWorkerController($this->serverId);
            $worker->channelRemoverWorker();
        } catch (Exception $e) {
            $tsLogging = new TsLogController('Channel-Remover-Worker', $this->serverId);
            $tsLogging->setCustomLog(
                $this->serverId,
                tsBotLog::FAILED,
                'queue_worker',
                'There was an error during create queue',
                $e->getCode(),
                $e->getMessage()
            );
        }
    }

    public function uniqueId(): int
    {
        return $this->serverId;
    }

    public function backoff(): int
    {
        return $this->backoff;
    }
}
