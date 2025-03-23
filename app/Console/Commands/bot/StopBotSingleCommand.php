<?php

namespace App\Console\Commands\bot;

use App\Http\Controllers\sys\Ts3LogController;
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
    protected $signature = 'app:stop_bot {serverID}';

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
        //get bot config
        $runningBot = ts3ServerConfig::query()
            ->where('id', '=', $this->argument('serverID'))
            ->first();

        //set stop
        $logController = new Ts3LogController('ServerCLI', $runningBot->id);
        $logController->setCustomLog(
            $runningBot->id,
            ts3BotLog::SUCCESS,
            'BotUpdateProcess',
            'Bot wird via CLI gestoppt'
        );

        ts3ServerConfig::query()
            ->where('id', '=', $runningBot->id)
            ->update([
                'is_ts3_start'=>false,
                'is_active'=>false,
            ]);
    }
}
