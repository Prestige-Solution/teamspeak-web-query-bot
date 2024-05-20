<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ts3BotWorkers\ts3BotWorkerPolice;

class ResetStatsController extends Controller
{
    public function resetStats()
    {
        //goto function to reset statistics
    }

    public function resetVPNQueryCountPerMinute(): void
    {
        ts3BotWorkerPolice::query()->update([
            'vpn_protection_query_count'=>0,
        ]);
    }

    public function resetVPNQueryPerDay(): void
    {
        ts3BotWorkerPolice::query()->update([
            'vpn_protection_query_per_day'=>0,
        ]);
    }
}
