<?php

namespace App\Console\Commands\bot;

use App\Http\Controllers\sys\Ts3LogController;
use App\Models\ts3Bot\ts3BotLog;
use App\Models\ts3Bot\ts3ServerConfig;
use Illuminate\Console\Command;

class StopBotAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:stop_bots';

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
        //get all running bots
        $runningBots = ts3ServerConfig::query()
            ->where('is_ts3_start', '=', true)
            ->where('is_active', '=', true)
            ->get();

        //set for each server stop status and log
        foreach ($runningBots as $runningBot) {
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
                    'is_system_running_before_update'=>true,
                    'is_ts3_start'=>false,
                    'is_active'=>false,
                    'is_bot_update'=>true,
                ]);
        }
    }
}
