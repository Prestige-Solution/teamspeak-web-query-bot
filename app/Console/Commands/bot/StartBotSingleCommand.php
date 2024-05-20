<?php

namespace App\Console\Commands\bot;

use App\Http\Controllers\sys\Ts3LogController;
use App\Jobs\ts3BotStartJob;
use App\Models\ts3Bot\ts3ServerConfig;
use Illuminate\Console\Command;

class StartBotSingleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:start_bot {serverID}';

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
            1,
            'Bot start process',
            'Bot wurde via CLI gestartet',
            null,
            null,
        );

        ts3ServerConfig::query()->where('id','=',$this->argument('serverID'))->update([
            'ts3_start_stop'=>true,
            'active'=>true,
        ]);

        $botConfig = ts3ServerConfig::query()->where('id','=',$this->argument('serverID'))->first();

        if ($botConfig->ts3_start_stop == true && $botConfig->bot_confirmed == true)
        {
            //create bot class
            ts3BotStartJob::dispatch($this->argument('serverID'))->onConnection('bot')->onQueue('default');
        }
    }
}
