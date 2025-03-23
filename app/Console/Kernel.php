<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        //define afk worker schedule
        if (config('app.env') === 'development') {
            $schedule->command('app:start-worker');
            $schedule->command('app:start-clearing');
        } else {
            $schedule->command('app:start-worker')->everyMinute();
            $schedule->command('app:start-clearing')->everyFifteenMinutes();
            $schedule->call('App\Http\Controllers\admin\ResetStatsController@resetVPNQueryCountPerMinute')->everyMinute();
            $schedule->call('App\Http\Controllers\admin\ResetStatsController@resetVPNQueryPerDay')->daily();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
