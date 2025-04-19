<?php

use App\Http\Controllers\admin\ResetStatsController;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/
if (config('app.env') === 'development') {
    Schedule::command('app:start-worker');
    Schedule::command('app:start-clearing');

    Schedule::call(function () {
        $statsController = new ResetStatsController();
        $statsController->resetVPNQueryCountPerMinute();
    })->name('reset vpn query count per minute');

    Schedule::call(function () {
        $statsController = new ResetStatsController();
        $statsController->resetVPNQueryPerDay();
    })->name('reset vpn query count per day');

    Schedule::call(function () {
        $statsController = new ResetStatsController();
        $statsController->deleteBotLogs();
    })->name('run delete latest bot logs');
} else {
    Schedule::command('app:start-worker')->everyMinute();
    Schedule::command('app:start-clearing')->everyFifteenMinutes();

    Schedule::call(function () {
        $statsController = new ResetStatsController();
        $statsController->resetVPNQueryCountPerMinute();
    })->name('reset vpn query count per minute')->everyMinute();

    Schedule::call(function () {
        $statsController = new ResetStatsController();
        $statsController->resetVPNQueryPerDay();
    })->name('reset vpn query count per day')->daily();

    Schedule::call(function () {
        $statsController = new ResetStatsController();
        $statsController->deleteBotLogs();
    })->name('run delete latest bot logs')->everyMinute();
}
