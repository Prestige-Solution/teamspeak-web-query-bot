<?php

namespace App\Console\Commands\bot;

use App\Http\Controllers\bot\Ts3BotController;
use App\Models\ts3Bot\ts3ServerConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class StartBotInstanceCommand extends Command
{
    protected $signature = 'app:start-bot-instance-command';

    protected $description = 'start a single bot instance';

    public function handle(): int
    {
        $serverIds = $this->getServerIds();

        $lockDirectory = storage_path('app/bot-locks');
        if (! File::isDirectory($lockDirectory)) {
            File::makeDirectory($lockDirectory, 0777, true);
        }

        foreach ($serverIds as $serverId) {
            $lockFilePath = $lockDirectory.'/ts3-bot-'.$serverId.'.lock';
            $lockHandle = fopen($lockFilePath, 'c+');

            if ($lockHandle === false) {
                $this->error('lock file could not be opened for server_id='.$serverId);

                continue;
            }

            if (! flock($lockHandle, LOCK_EX | LOCK_NB)) {
                $this->warn('bot is already running for server_id='.$serverId);
                fclose($lockHandle);

                continue;
            }

            fwrite($lockHandle, (string) getmypid());
            fflush($lockHandle);

            try {
                new Ts3BotController($serverId);

                return self::SUCCESS;
            } finally {
                flock($lockHandle, LOCK_UN);
                fclose($lockHandle);
            }
        }
        
        sleep(30);
        return self::SUCCESS;
    }

    private function getServerIds(): array
    {
        return ts3ServerConfig::query()
            ->where('is_active', '=', true)
            ->where('is_ts3_start', '=', true)
            ->pluck('id')->toArray();
    }
}
