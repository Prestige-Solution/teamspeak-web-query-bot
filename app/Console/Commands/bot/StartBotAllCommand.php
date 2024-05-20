<?php

namespace App\Console\Commands\bot;

use App\Http\Controllers\sys\Ts3LogController;
use App\Jobs\ts3BotStartJob;
use App\Models\ts3Bot\ts3ServerConfig;
use Illuminate\Console\Command;

class StartBotAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:start_bots';

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
        //get all bots with stopped status
        $startBots = ts3ServerConfig::query()
            ->where('system_running_before_update','=',true)
            ->get();

        foreach ($startBots as $startBot)
        {
            $logController = new Ts3LogController('ServerCLI', $startBot->id);
            $logController->setCustomLog(
                $startBot->id,
                5,
                'BotUpdateProcess',
                'Bot wird via CLI gestartet',
                null,
                null,
            );

            //set status
            ts3ServerConfig::query()->where('id','=',$startBot->id)->update([
                'ts3_start_stop'=>true,
                'active'=>true,
                'system_running_before_update'=>false,
                'bot_update'=>false,
            ]);

            //start bot
            ts3BotStartJob::dispatch($startBot->id)->onConnection('bot')->onQueue('default');
        }
    }
}
