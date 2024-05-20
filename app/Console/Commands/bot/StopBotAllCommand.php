<?php

namespace App\Console\Commands\bot;

use App\Http\Controllers\sys\Ts3LogController;
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
            ->where('ts3_start_stop','=',true)
            ->where('active','=',true)
            ->get();

        //set for each server stop status and log
        foreach ($runningBots as $runningBot)
        {
            $logController = new Ts3LogController('ServerCLI',$runningBot->id);
            $logController->setCustomLog(
                $runningBot->id,
                5,
                'BotUpdateProcess',
                'Bot wird via CLI gestoppt',
                null,
                null,
            );

            ts3ServerConfig::query()
                ->where('id','=',$runningBot->id)
                ->update([
                    'system_running_before_update'=>true,
                    'ts3_start_stop'=>false,
                    'active'=>false,
                    'bot_update'=>true,
                ]);
        }
    }
}
