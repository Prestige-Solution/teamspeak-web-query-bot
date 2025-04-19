<?php

namespace App\Console\Commands\bot;

use App\Http\Controllers\sys\Ts3LogController;
use App\Jobs\ts3BotStartQueue;
use App\Models\ts3Bot\ts3BotLog;
use App\Models\ts3Bot\ts3ServerConfig;
use Illuminate\Console\Command;

class StartBotSingleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:start-bot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start bot instance';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $choice = ts3ServerConfig::query()
            ->where('is_ts3_start', '=', false)
            ->orderBy('server_ip')
            ->get();

        if ($choice->isNotEmpty()) {
            $choice = $choice->pluck('server_ip')->toArray();
        } else {
            $this->info('There no instances available or all marked as started');

            return;
        }

        $instanceResult = $this->choice(
            'Which instance should be started?',
            $choice,
            null,
            2
        );

        $server_id = ts3ServerConfig::query()->where('server_ip', '=', $instanceResult)->get()->first()->id;
        $this->start_single_instance($server_id);

        $logController = new Ts3LogController('CLI-Commands', $server_id);

        $logController->setCustomLog(
            $server_id,
            ts3BotLog::SUCCESS,
            'startBot',
            'Bot was started via cli'
        );

        $this->info('Bot is starting');
    }

    private function start_single_instance(int $server_id): void
    {
        ts3ServerConfig::query()->where('id', '=', $server_id)->update([
            'is_ts3_start'=>true,
            'is_active'=>true,
        ]);

        ts3BotStartQueue::dispatch($server_id)->onConnection('bot')->onQueue('bot');
    }
}
