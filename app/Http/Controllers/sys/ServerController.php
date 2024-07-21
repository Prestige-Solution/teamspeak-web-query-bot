<?php

namespace App\Http\Controllers\sys;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ts3Config\Ts3ConfigController;
use App\Http\Requests\Config\CreateServerRequest;
use App\Http\Requests\Config\DeleteServerRequest;
use App\Http\Requests\Config\SwitchDefaultServerRequest;
use App\Http\Requests\Config\UpdateServerInitRequest;
use App\Http\Requests\Config\UpdateServerRequest;
use App\Models\ts3Bot\ts3Channel;
use App\Models\ts3Bot\ts3ChannelGroup;
use App\Models\ts3Bot\ts3ServerConfig;
use App\Models\ts3Bot\ts3ServerGroup;
use App\Models\ts3Bot\ts3UserDatabase;
use App\Models\ts3BotJobs\ts3BotJobCreateChannels;
use App\Models\ts3BotWorkers\ts3BotWorkerAfk;
use App\Models\ts3BotWorkers\ts3BotWorkerChannelRemover;
use App\Models\ts3BotWorkers\ts3BotWorkerPolice;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class ServerController extends Controller
{
    public function viewServerList(): Factory|View|Application
    {
        $servers = ts3ServerConfig::query()->orderBy('server_ip')->get();

        return view('backend.server.servers')->with([
            'servers'=>$servers,
        ]);
    }

    public function createServer(CreateServerRequest $request): \Illuminate\Http\RedirectResponse
    {
        ts3ServerConfig::query()->create(
            [
                'user_id'=>Auth::user()->id,
                'server_ip'=>$request->validated('ServerIP'),
                'server_name'=>$request->validated('ServerName'),
                'qa_name'=>$request->validated('QaName'),
                'qa_pw'=>Crypt::encryptString(($request->validated('QaPW'))),
                'server_query_port'=>$request->validated('ServerQueryPort') ?? 10011,
                'server_port'=>$request->validated('ServerPort') ?? 9987,
                'description'=>$request->validated('Description'),
                'qa_nickname'=>str_replace(' ','',$request->input('QueryNickname')),
                'mode'=>$request->validated('ConMode'),
                'bot_confirmed'=>true,
                'bot_confirmed_at'=>Carbon::now()->format('Y-m-d H:i:s'),
            ]
        );

        $newCreatedServerID = ts3ServerConfig::query()->get(['id']);

        //setup police worker
        ts3BotWorkerPolice::query()->create([
            'server_id'=>$newCreatedServerID->last()->id,
        ]);

        if ($newCreatedServerID->count() == 1)
        {
            ts3ServerConfig::query()->where('id','=',$newCreatedServerID->last()->id)->update([
                'default'=>true,
            ]);
            User::query()->where('id','=', Auth::user()->id)->update(['server_id' => $newCreatedServerID->last()->id]);
        }

        return redirect()->route('serverConfig.view.serverList');
    }

    public function updateServer(UpdateServerRequest $request): \Illuminate\Http\RedirectResponse
    {
        ts3ServerConfig::query()->where('id','=',$request->validated('ServerID'))->update(
            [
                'server_ip'=>$request->validated('ServerIP'),
                'server_name'=>$request->validated('ServerName'),
                'qa_name'=>$request->validated('QaName'),
                'qa_pw'=>Crypt::encryptString(($request->validated('QaPW'))),
                'server_query_port'=>$request->validated('ServerQueryPort') ?? 10011,
                'server_port'=>$request->validated('ServerPort') ?? 9987,
                'description'=>$request->input('Description'),
                'qa_nickname'=>str_replace(' ','',$request->input('QueryNickname')),
                'mode'=>$request->validated('ConMode'),
            ]
        );

        return redirect()->route('serverConfig.view.serverList');
    }

    public function updateServerInit(UpdateServerInitRequest $request)
    {
        $server = ts3ServerConfig::query()
            ->where('id', '=', $request->validated('ServerID'))
            ->first();

        if ($server !== null) {
            $reInit = new Ts3ConfigController();
            $returnCode = $reInit->ts3ServerInitializing($request->validated('ServerID'));

            if ($returnCode['status'] == 1) {
                return redirect()->back()->with('success', 'Der Server wurde erfolgreich neu eingerichtet');
            } else {
                return redirect()->back()->withErrors(['error' => $returnCode['msg']]);
            }
        }

        //TODO define default back statement
        return redirect()->back();
    }

    public function updateSwitchDefaultServer(SwitchDefaultServerRequest $request): \Illuminate\Http\RedirectResponse
    {
        //normalize
        ts3ServerConfig::query()->where('default','=',true)->update([
            'default'=>false,
        ]);

        ts3ServerConfig::query()->where('id','=',$request->validated('ServerID'))->update([
            'default'=>true,
        ]);

        User::query()->where('id','=', Auth::user()->id)->update(['server_id' => $request->validated('ServerID')]);

        return redirect()->back();
    }

    public function deleteServer(DeleteServerRequest $request): \Illuminate\Http\RedirectResponse
    {
        //TODO Delete Banner config an data

        //delete all relations ins used tables
        ts3Channel::query()->where('server_id','=',$request->validated('ServerID'))->delete();
        ts3ServerGroup::query()->where('server_id','=',$request->validated('ServerID'))->delete();
        ts3ChannelGroup::query()->where('server_id','=',$request->validated('ServerID'))->delete();
        ts3UserDatabase::query()->where('server_id','=',$request->validated('ServerID'))->delete();
        ts3BotJobCreateChannels::query()->where('server_id','=',$request->validated('ServerID'))->delete();
        ts3BotWorkerAfk::query()->where('server_id','=',$request->validated('ServerID'))->delete();
        ts3BotWorkerChannelRemover::query()->where('server_id','=',$request->validated('ServerID'))->delete();
        ts3BotWorkerPolice::query()->where('server_id','=',$request->validated('ServerID'))->delete();

        //delete server from table server configs
        ts3ServerConfig::query()->where('id','=',$request->validated('ServerID'))->delete();

        //TODO switch to an active Server if available

        return redirect()->route('serverConfig.view.serverList');
    }
}
