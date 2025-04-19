<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ts3Bot\ts3BotLog;
use App\Models\ts3Bot\ts3ServerConfig;
use App\Models\ts3BotWorkers\ts3BotWorkerPolice;

class ResetStatsController extends Controller
{
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

    public function deleteBotLogs(): void
    {
        $servers = ts3ServerConfig::query()->get(['id']);

        foreach ($servers as $server) {
            $count = ts3BotLog::query()->where(['server_id' => $server->id])->get()->count();
            if ($count > 100) {
                ts3BotLog::query()
                    ->where('server_id', $server->id)
                    ->orderByDesc('id')
                    ->limit($count)
                    ->offset(100)
                    ->delete();
            }
        }
    }
}
