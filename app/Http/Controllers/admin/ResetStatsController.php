<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\tsBot\tsBotLog;
use App\Models\tsBot\tsServerConfig;
use App\Models\tsBotWorkers\tsBotWorkerPolice;

class ResetStatsController extends Controller
{
    public function resetVPNQueryCountPerMinute(): void
    {
        tsBotWorkerPolice::query()->update([
            'vpn_protection_query_count'=>0,
        ]);
    }

    public function resetVPNQueryPerDay(): void
    {
        tsBotWorkerPolice::query()->update([
            'vpn_protection_query_per_day'=>0,
        ]);
    }

    public function deleteBotLogs(): void
    {
        $servers = tsServerConfig::query()->get(['id']);

        foreach ($servers as $server) {
            $count = tsBotLog::query()
                ->where('server_id', '=', $server->id)
                ->count();

            if ($count > 100) {
                $latestLogIds = tsBotLog::query()
                    ->where('server_id', '=', $server->id)
                    ->orderByDesc('id')
                    ->limit(100)
                    ->pluck('id');

                tsBotLog::query()
                    ->where('server_id', '=', $server->id)
                    ->whereNotIn('id', $latestLogIds)
                    ->delete();
            }
        }
    }
}
