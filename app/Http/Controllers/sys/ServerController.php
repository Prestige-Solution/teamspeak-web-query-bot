<?php

namespace App\Http\Controllers\sys;

use App\Http\Controllers\banner\BannerController;
use App\Http\Controllers\channel\ChannelController;
use App\Http\Controllers\channel\ChannelRemoverController;
use App\Http\Controllers\client\ClientController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\tsConfig\BadNameController;
use App\Http\Controllers\tsConfig\TsConfigController;
use App\Http\Requests\Config\CreateServerRequest;
use App\Http\Requests\Config\DeleteServerRequest;
use App\Http\Requests\Config\SwitchDefaultServerRequest;
use App\Http\Requests\Config\UpdateServerInitRequest;
use App\Http\Requests\Config\UpdateServerRequest;
use App\Models\sys\statistic;
use App\Models\tsBot\tsChannel;
use App\Models\tsBot\tsChannelGroup;
use App\Models\tsBot\tsServerConfig;
use App\Models\tsBot\tsServerGroup;
use App\Models\tsBotWorkers\tsBotWorkerPolice;
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
        $servers = tsServerConfig::query()->orderBy('server_ip')->get();

        return view('backend.server.servers')->with([
            'servers'=>$servers,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function createServer(CreateServerRequest $request): \Illuminate\Http\RedirectResponse
    {
        $server_id = tsServerConfig::query()->create(
            [
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

        //set created new server as active
        if (! empty($server_id)) {
            User::query()->where('id', '=', Auth::user()->id)->update(['active_server_id' => $server_id]);
        }else{
            return redirect()->back()->withErrors(['error' => 'Server creation failed']);
        }

        //initializing server only in production mode
        if (config('app.env') !== 'testing') {
            $status = $this->initialisingTsServer($server_id);

            if ($status != 0) {
                if ($status['status'] == 1) {
                    return redirect()->route('serverConfig.view.serverList')->with('success', 'The server has been set up successfully');
                } else {
                    return redirect()->back()->withErrors(['error' => $status['msg']]);
                }
            }
        }

        return redirect()->route('serverConfig.view.serverList');
    }

    public function updateServer(UpdateServerRequest $request): \Illuminate\Http\RedirectResponse
    {
        tsServerConfig::query()->where('id', '=', $request->validated('server_id'))->update(
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
        $status = $this->initialisingTsServer($request->validated('server_id'), true);

        if ($status != 0) {
            if ($status['status'] == 1) {
                return redirect()->back()->with('success', 'The server has been successfully reconfigured');
            } else {
                return redirect()->back()->withErrors(['error' => $status['msg']]);
            }
        }

        return redirect()->back();
    }

    public function updateSwitchDefaultServer(SwitchDefaultServerRequest $request): \Illuminate\Http\RedirectResponse
    {
        User::query()->where('id', '=', Auth::user()->id)->update(['active_server_id' => $request->validated('server_id')]);

        return redirect()->back()->with('success', 'Server switched successfully');
    }

    public function deleteServer(DeleteServerRequest $request): \Illuminate\Http\RedirectResponse
    {
        //delete logs and stats
        $this->deleteBotLogs($request->validated('server_id'));
        $this->deleteStatistics($request->validated('server_id'));

        //delete all worker configs
        $this->deleteWorkerConfigs($request->validated('server_id'));
        $this->deleteBadNameEntrys($request->validated('server_id'));

        //delete ts data
        $this->deleteTsDatabaseEntrys($request->validated('server_id'));

        //delete server banner
        $this->deleteBanners($request->validated('server_id'));

        //delete server
        tsServerConfig::query()->where('id', '=', $request->validated('server_id'))->delete();

        //check if a server is available else set user active_server_id to 0
        $serverlist = tsServerConfig::query()->get();

        if ($serverlist->count() > 0) {
            User::query()->update(['active_server_id' => $serverlist->first()->id]);
        } else {
            User::query()->update(['active_server_id' => 0]);
        }

        return redirect()->route('serverConfig.view.serverList');
    }

    /**
     * @param  int|null  $server_id
     * @throws \Exception
     */
    private function initialisingTsServer(int $server_id = null, bool $update = false): array|int
    {
        //if create new server
        if ($update === false)
        {
            //create default entry in police worker
            tsBotWorkerPolice::query()->create(['server_id'=>$server_id]);

            //create default entry in statistics
            statistic::query()->firstOrCreate(['server_id'=>$server_id]);

            $reInit = new TsConfigController();
            $returnCode = $reInit->tsServerInitializing($server_id);
        }

        if ($update === true && $server_id !== null) {
            //delete logs and stats
            $this->deleteBotLogs($server_id);
            $this->deleteStatistics($server_id);

            //delete all worker configs
            $this->deleteWorkerConfigs($server_id);
            $this->deleteBadNameEntrys($server_id);

            //delete ts data
            $this->deleteTsDatabaseEntrys($server_id);

            //delete server banner
            $this->deleteBanners($server_id);

            //create default entry in police worker
            tsBotWorkerPolice::query()->firstOrCreate(['server_id'=>$server_id]);

            //create default entry in statistics
            statistic::query()->firstOrCreate(['server_id'=>$server_id]);

            $reInit = new TsConfigController();
            $returnCode = $reInit->tsServerInitializing($server_id);
        }

        return $returnCode ?? 0;
    }

    private function deleteTsDatabaseEntrys(int $server_id): void
    {
        tsChannel::query()->where('server_id', '=', $server_id)->delete();
        tsServerGroup::query()->where('server_id', '=', $server_id)->delete();
        tsChannelGroup::query()->where('server_id', '=', $server_id)->delete();
    }

    private function deleteWorkerConfigs(int $server_id): void
    {
        $channelCreateJobsController = new ChannelController();
        $channelCreateJobsController->deleteChannelCreateJobsByServerId($server_id);

        $channelRemoveJobsController = new ChannelRemoverController();
        $channelRemoveJobsController->deleteChannelRemoveJobsByServerId($server_id);

        $clientController = new ClientController();
        $clientController->deleteAfkWorkerSettingsByServerId($server_id);
        $clientController->deletePoliceWorkerSettingsByServerId($server_id);
        $clientController->deletePoliceVpnProtectionWorkerSettingsByServerId($server_id);

    }

    private function deleteBotLogs(int $server_id): void
    {
        $botLogsController = new TsLogController('ServerController',$server_id);
        $botLogsController->deleteLogEntrysByServerID();
    }

    private function deleteBadNameEntrys(int $server_id): void
    {
        $badNamesController = new BadNameController();
        $badNamesController->deleteBadNameEntrysByServerID($server_id);
    }

    private function deleteStatistics(int $server_id): void
    {
        $statisticsController = new StatisticController();
        $statisticsController->deleteStatisticsByServerID($server_id);
    }

    private function deleteBanners(int $server_id): void
    {
        $bannerController = new BannerController();
        $bannerController->deleteBannersByServerID($server_id);
    }
}
