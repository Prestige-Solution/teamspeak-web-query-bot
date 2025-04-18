<?php

namespace App\Http\Controllers;

use App\Models\ts3Bot\ts3BotLog;
use App\Models\ts3Bot\ts3ServerConfig;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class BackendController extends Controller
{
    public function viewBackendDashboard(): View|Factory|RedirectResponse|Application
    {
        //check if user has a default_server_id
        if (Auth::user()->default_server_id === 0) {
            return view('backend.start');
        } else {
            return redirect()->route('backend.view.botControlCenter', ['server_id'=>Auth::user()->default_server_id]);
        }
    }

    public function viewBotControlCenter(): Factory|View|RedirectResponse|Application
    {
        $server = ts3ServerConfig::query()
            ->with('rel_bot_status')
            ->where('is_default', '=', true)
            ->first();

        $availableServers = ts3ServerConfig::query()->orderBy('server_ip')->get(['id', 'server_name']);

        return view('backend.control-center.bot-control')->with([
            'server'=>$server,
            'availableServers'=>$availableServers,
        ]);
    }

    public function viewBotLogs(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $botLogs = ts3BotLog::query()->with('rel_bot_status')
            ->where('server_id', '=', Auth::user()->default_server_id)
            ->where('job', '!=', 'queuingWorkers')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('backend.control-center.bot-logs')->with([
            'botLogs'=>$botLogs,
        ]);
    }

    public function viewChangePassword(): \Illuminate\Foundation\Application|View|Factory|Application
    {
        return view('auth.changePassword');
    }
}
