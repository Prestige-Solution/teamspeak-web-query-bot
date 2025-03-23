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
use App\Models\ts3BotWorkers\ts3BotWorkerAfk;
use App\Models\ts3BotWorkers\ts3BotWorkerChannelsCreate;
use App\Models\ts3BotWorkers\ts3BotWorkerChannelsRemove;
use App\Models\ts3BotWorkers\ts3BotWorkerPolice;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class ServerController extends Controller
{
    /**
     * @return Factory|View|Application
     */
    public function viewServerList(): Factory|View|Application
    {
        $servers = ts3ServerConfig::query()->orderBy('server_ip')->get();

        return view('backend.server.servers')->with([
            'servers'=>$servers,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function createServer(CreateServerRequest $request): \Illuminate\Http\RedirectResponse
    {
        $serverID = ts3ServerConfig::query()->create(
            [
                'user_id'=>Auth::user()->id,
                'server_ip'=>$request->validated('server_ip'),
                'server_name'=>$request->validated('server_name'),
                'qa_name'=>$request->validated('qa_name'),
                'qa_pw'=>Crypt::encryptString($request->validated('qa_pw')),
                'server_query_port'=>$request->validated('server_query_port') ?? null,
                'server_port'=>$request->validated('server_port') ?? 9987,
                'description'=>$request->validated('description'),
                'qa_nickname'=>$request->input('qa_nickname'),
                'mode'=>$request->validated('mode'),
            ]
        )->id;

        //setup police worker
        ts3BotWorkerPolice::query()->create([
            'server_id'=>$serverID,
        ]);

        if (! empty($serverID)) {
            ts3ServerConfig::query()->where('id', '=', $serverID)->update([
                'is_default'=>true,
            ]);

            User::query()->where('id', '=', Auth::user()->id)->update(['default_server_id' => $serverID]);
        }

        //initialising server only in production mode
        if (config('app.env') !== 'testing') {
            $status = $this->initialisingTs3Server($serverID);

            if ($status != 0) {
                if ($status['status'] == 1) {
                    return redirect()->route('serverConfig.view.serverList')->with('success', 'Der Server wurde erfolgreich neu eingerichtet');
                } else {
                    return redirect()->back()->withErrors(['error' => $status['msg']]);
                }
            }
        }

        return redirect()->route('serverConfig.view.serverList');
    }

    public function updateServer(UpdateServerRequest $request): \Illuminate\Http\RedirectResponse
    {
        ts3ServerConfig::query()->where('id', '=', $request->validated('server_id'))->update(
            [
                'server_ip'=>$request->validated('server_ip'),
                'server_name'=>$request->validated('server_name'),
                'qa_name'=>$request->validated('qa_name'),
                'qa_pw'=>Crypt::encryptString(($request->validated('qa_pw'))),
                'server_query_port'=>$request->validated('server_query_port') ?? null,
                'server_port'=>$request->validated('server_port') ?? 9987,
                'description'=>$request->input('description'),
                'qa_nickname'=>str_replace(' ', '', $request->input('qa_nickname')),
                'mode'=>$request->validated('mode'),
            ]
        );

        return redirect()->route('serverConfig.view.serverList');
    }

    /**
     * @throws \Exception
     */
    public function updateServerInit(UpdateServerInitRequest $request): \Illuminate\Http\RedirectResponse
    {
        $status = $this->initialisingTs3Server($request->validated('server_id'));

        if ($status != 0) {
            if ($status['status'] == 1) {
                return redirect()->back()->with('success', 'Der Server wurde erfolgreich neu eingerichtet');
            } else {
                return redirect()->back()->withErrors(['error' => $status['msg']]);
            }
        }

        return redirect()->back();
    }

    public function updateSwitchDefaultServer(SwitchDefaultServerRequest $request): \Illuminate\Http\RedirectResponse
    {
        //normalize
        ts3ServerConfig::query()->where('is_default', '=', true)->update([
            'is_default'=>false,
        ]);

        ts3ServerConfig::query()->where('id', '=', $request->validated('server_id'))->update([
            'is_default'=>true,
        ]);

        User::query()->where('id', '=', Auth::user()->id)->update(['default_server_id' => $request->validated('server_id')]);

        return redirect()->route('serverConfig.view.serverList');
    }

    public function deleteServer(DeleteServerRequest $request): \Illuminate\Http\RedirectResponse
    {
        //TODO Delete Banner config an data

        //delete all relations ins used tables
        ts3Channel::query()->where('server_id', '=', $request->validated('server_id'))->delete();
        ts3ServerGroup::query()->where('server_id', '=', $request->validated('server_id'))->delete();
        ts3ChannelGroup::query()->where('server_id', '=', $request->validated('server_id'))->delete();
        ts3UserDatabase::query()->where('server_id', '=', $request->validated('server_id'))->delete();
        ts3BotWorkerChannelsCreate::query()->where('server_id', '=', $request->validated('server_id'))->delete();
        ts3BotWorkerAfk::query()->where('server_id', '=', $request->validated('server_id'))->delete();
        ts3BotWorkerChannelsRemove::query()->where('server_id', '=', $request->validated('server_id'))->delete();
        ts3BotWorkerPolice::query()->where('server_id', '=', $request->validated('server_id'))->delete();

        //delete server from table server configs
        ts3ServerConfig::query()->where('id', '=', $request->validated('server_id'))->delete();

        //Check if a server available else set 0
        $serverlist = ts3ServerConfig::query()->get();

        if ($serverlist->count() > 0) {
            User::query()->update(['default_server_id' => $serverlist->first()->id]);
        } else {
            User::query()->update(['default_server_id' => 0]);
        }

        return redirect()->route('serverConfig.view.serverList');
    }

    /**
     * @param  int|null  $server_id
     * @throws \Exception
     */
    private function initialisingTs3Server(int $server_id = null): array|int
    {
        if ($server_id !== null) {
            $reInit = new Ts3ConfigController();
            $returnCode = $reInit->ts3ServerInitializing($server_id);
        }

        return $returnCode ?? 0;
    }
}
