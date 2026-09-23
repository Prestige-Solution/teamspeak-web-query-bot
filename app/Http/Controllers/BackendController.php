<?php

namespace App\Http\Controllers;

use App\Http\Requests\Backend\UpdateChangePasswordRequest;
use App\Models\sys\statistic;
use App\Models\tsBot\tsBotLog;
use App\Models\tsBot\tsServerConfig;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class BackendController extends Controller
{
    public function viewBackendDashboard(): View|Factory|RedirectResponse|Application
    {
        $stats = statistic::query()
            ->where('server_id', '=', Auth::user()->active_server_id)
            ->first();

        $server = tsServerConfig::query()
            ->with('rel_bot_status')
            ->where('id', '=', Auth::user()->active_server_id)
            ->first();

        $availableServers = tsServerConfig::query()
            ->orderBy('server_ip')
            ->get(['id', 'server_name']);

        $botLogs = tsBotLog::query()->with('rel_bot_status')
            ->where('server_id', '=', Auth::user()->active_server_id)
            ->where('job', '!=', 'queuingWorkers')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('backend.dashboard.dashboard')->with([
            'stats'=> $stats,
            'server'=> $server,
            'availableServers'=>$availableServers,
            'botLogs' => $botLogs,
        ]);
    }

    public function viewBotControlCenter(): Factory|View|RedirectResponse|Application
    {
        $server = tsServerConfig::query()
            ->with('rel_bot_status')
            ->where('id', '=', Auth::user()->active_server_id)
            ->first();

        $availableServers = tsServerConfig::query()->orderBy('server_ip')->get(['id', 'server_name']);

        return view('backend.control-center.bot-control')->with([
            'server'=>$server,
            'availableServers'=>$availableServers,
        ]);
    }

    public function viewBotLogs(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $server = tsServerConfig::query()
            ->with('rel_bot_status')
            ->where('id', '=', Auth::user()->active_server_id)
            ->first();

        $availableServers = tsServerConfig::query()
            ->orderBy('server_ip')
            ->get(['id', 'server_name']);

        $botLogs = tsBotLog::query()->with('rel_bot_status')
            ->where('server_id', '=', Auth::user()->active_server_id)
            ->where('job', '!=', 'queuingWorkers')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('backend.control-center.bot-logs')->with([
            'botLogs'=>$botLogs,
            'server'=> $server,
            'availableServers'=>$availableServers,
        ]);
    }

    public function viewChangePassword(): \Illuminate\Foundation\Application|View|Factory|Application
    {
        return view('auth.changePassword');
    }

    public function updateChangePassword(UpdateChangePasswordRequest $request): RedirectResponse
    {
        User::query()
            ->where('id', '=', Auth::user()->id)
            ->update([
                'password' => Hash::make($request->validated('NewPassword')),
            ]);

        return redirect()->route('backend.view.dashboard')->with(['success'=>'Password changed successfully.']);
    }
}
