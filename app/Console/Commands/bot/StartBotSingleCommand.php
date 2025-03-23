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
    protected $signature = 'app:start-bot {serverID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $logController = new Ts3LogController('ServerCLI', $this->argument('serverID'));
        $logController->setCustomLog(
            $this->argument('serverID'),
            ts3BotLog::RUNNING,
            'Bot start process',
            'Bot wurde via CLI gestartet'
        );

        ts3ServerConfig::query()->where('id', '=', $this->argument('serverID'))->update([
            'is_ts3_start'=>true,
            'is_active'=>true,
        ]);

        $botConfig = ts3ServerConfig::query()->where('id', '=', $this->argument('serverID'))->first();

        if ($botConfig->is_ts3_start == true) {
            //create bot class
            ts3BotStartQueue::dispatch($this->argument('serverID'))->onConnection('bot')->onQueue('bot');
        }
    }
}
