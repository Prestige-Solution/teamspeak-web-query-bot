<?php

namespace App\Http\Controllers\sys;

use App\Http\Controllers\Controller;
use App\Models\ts3Bot\ts3ServerConfig;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class ServerController extends Controller
{
    public function viewServerList(): Factory|View|Application
    {
        $servers = ts3ServerConfig::query()->get();

        return view('admin.server.list-server')->with([
            'servers'=>$servers,
        ]);
    }
}
