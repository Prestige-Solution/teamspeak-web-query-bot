<?php

namespace App\Console\Commands\bot;

use App\Jobs\ts3BotStartJob;
use App\Models\ts3Bot\ts3ServerConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class StartBotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:start_bot {serverID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starting TS3 Bot Connection';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $botConfig = ts3ServerConfig::query()->where('id','=',$this->argument('serverID'))->first();

        if ($botConfig->ts3_start_stop == true && $botConfig->bot_confirmed == true)
        {
            //create bot class
            ts3BotStartJob::dispatch($this->argument('serverID'))->onConnection('bot')->onQueue('default');
        }
    }
}
