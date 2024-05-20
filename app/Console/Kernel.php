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
    protected function schedule(Schedule $schedule)
    {
        //define afk worker schedule
        $schedule->command('command:start_ts3Worker')->everyMinute();
        $schedule->command('app:start_worker_clearing')->everyFifteenMinutes();
        $schedule->call('App\Http\Controllers\admin\ResetStatsController@resetVPNQueryCountPerMinute')->everyMinute();
        $schedule->call('App\Http\Controllers\admin\ResetStatsController@resetVPNQueryPerDay')->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
