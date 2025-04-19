<?php

namespace App\Console\Commands\bot;

use App\Http\Controllers\sys\Ts3LogController;
use App\Jobs\ts3BotStartQueue;
use App\Models\ts3Bot\ts3BotLog;
use App\Models\ts3Bot\ts3ServerConfig;
use Illuminate\Console\Command;

class StopBotSingleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:stop-bot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stop bot instance';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $choice = ts3ServerConfig::query()
            ->where('is_ts3_start', '=', true)
            ->orderBy('server_id')
            ->get();

        if ($choice->isNotEmpty()) {
            $choice = $choice->pluck('server_ip')->toArray();
        } else {
            $this->info('There are no running instances');

            return;
        }

        $instanceResult = $this->choice(
            'Which instance should be stopped?',
            $choice,
            null,
            2
        );

        $server_id = ts3ServerConfig::query()->where('server_ip', '=', $instanceResult)->get()->first()->id;
        $this->stop_single_instance($server_id);

        $logController = new Ts3LogController('CLI-Commands', $server_id);

        $logController->setCustomLog(
            $server_id,
            ts3BotLog::SUCCESS,
            'startBot',
            'Bot was stopped via cli'
        );

        $this->info('Bot is stopping');
    }

    private function stop_single_instance(int $server_id): void
    {
        ts3ServerConfig::query()->where('id', '=', $server_id)->update([
            'is_ts3_start'=>false,
            'is_active'=>false,
        ]);
    }
}
