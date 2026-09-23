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
        $serverId = tsServerConfig::query()->create(
            [
                'server_ip' => $request->validated('server_ip'),
                'server_name' => $request->validated('server_name'),
                'qa_name' => $request->validated('qa_name'),
                'qa_pw' => Crypt::encryptString($request->validated('qa_pw')),
                'server_query_port' => $request->validated('server_query_port') ?? null,
                'server_port' => $request->validated('server_port') ?? 9987,
                'description' => $request->validated('description'),
                'qa_nickname' => $request->input('qa_nickname'),
                'mode' => $request->validated('mode'),
            ]
        )->id;

        //set created new server as active
        if (! empty($serverId)) {
            User::query()->where('id', '=', Auth::user()->id)->update(['active_server_id' => $serverId]);
        } else {
            return redirect()->back()->withErrors(['error' => 'Server creation failed']);
        }

        //initializing server only in production mode
        if (config('app.env') !== 'testing') {
            $status = $this->initializeTsServer($serverId);

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
                'server_ip' => $request->validated('server_ip'),
                'server_name' => $request->validated('server_name'),
                'qa_name' => $request->validated('qa_name'),
                'qa_pw' => Crypt::encryptString(($request->validated('qa_pw'))),
                'server_query_port' => $request->validated('server_query_port') ?? null,
                'server_port' => $request->validated('server_port') ?? 9987,
                'description' => $request->input('description'),
                'qa_nickname' => str_replace(' ', '', $request->input('qa_nickname')),
                'mode' => $request->validated('mode'),
            ]
        );

        return redirect()->route('serverConfig.view.serverList');
    }

    /**
     * @throws \Exception
     */
    public function updateServerInit(UpdateServerInitRequest $request): \Illuminate\Http\RedirectResponse
    {
        $status = $this->initializeTsServer($request->validated('server_id'), true);

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
        $serverId = $request->validated('server_id');

        //delete logs and stats
        $this->deleteBotLogs($serverId);
        $this->deleteStatistics($serverId);

        //delete all worker configs
        $this->deleteWorkerConfigs($serverId);
        $this->deleteBadNameEntries($serverId);

        //delete ts data
        $this->deleteTsDatabaseEntries($serverId);

        //delete server banner
        $this->deleteBanners($serverId);

        //delete server
        tsServerConfig::query()->where('id', '=', $serverId)->delete();

        //check if a server is available else set user active_server_id to 0
        $serverList = tsServerConfig::query()->get();

        if ($serverList->count() > 0) {
            User::query()->update(['active_server_id' => $serverList->first()->id]);
        } else {
            User::query()->update(['active_server_id' => 0]);
        }

        return redirect()->route('serverConfig.view.serverList');
    }

    /**
     * @param  int|null  $serverId
     * @throws \Exception
     */
    private function initializeTsServer(?int $serverId = null, bool $update = false): array|int
    {
        //if create new server
        if ($update === false) {
            //create default entry in police worker
            tsBotWorkerPolice::query()->create(['server_id' => $serverId]);

            //create default entry in statistics
            statistic::query()->firstOrCreate(['server_id' => $serverId]);

            $reInit = new TsConfigController();
            $returnCode = $reInit->tsServerInitializing($serverId);
        }

        if ($update === true && $serverId !== null) {
            //delete logs and stats
            $this->deleteBotLogs($serverId);
            $this->deleteStatistics($serverId);

            //delete all worker configs
            $this->deleteWorkerConfigs($serverId);
            $this->deleteBadNameEntries($serverId);

            //delete ts data
            $this->deleteTsDatabaseEntries($serverId);

            //delete server banner
            $this->deleteBanners($serverId);

            //create default entry in police worker
            tsBotWorkerPolice::query()->firstOrCreate(['server_id' => $serverId]);

            //create default entry in statistics
            statistic::query()->firstOrCreate(['server_id' => $serverId]);

            $reInit = new TsConfigController();
            $returnCode = $reInit->tsServerInitializing($serverId);
        }

        return $returnCode ?? 0;
    }

    private function deleteTsDatabaseEntries(int $serverId): void
    {
        tsChannel::query()->where('server_id', '=', $serverId)->delete();
        tsServerGroup::query()->where('server_id', '=', $serverId)->delete();
        tsChannelGroup::query()->where('server_id', '=', $serverId)->delete();
    }

    private function deleteWorkerConfigs(int $serverId): void
    {
        $channelCreateJobsController = new ChannelController();
        $channelCreateJobsController->deleteChannelCreateJobsByServerId($serverId);

        $channelRemoveJobsController = new ChannelRemoverController();
        $channelRemoveJobsController->deleteChannelRemoveJobsByServerId($serverId);

        $clientController = new ClientController();
        $clientController->deleteAfkWorkerSettingsByServerId($serverId);
        $clientController->deletePoliceWorkerSettingsByServerId($serverId);
        $clientController->deletePoliceVpnProtectionWorkerSettingsByServerId($serverId);
    }

    private function deleteBotLogs(int $serverId): void
    {
        $botLogsController = new TsLogController('ServerController', $serverId);
        $botLogsController->deleteLogEntriesByServerId();
    }

    private function deleteBadNameEntries(int $serverId): void
    {
        $badNamesController = new BadNameController();
        $badNamesController->deleteBadNameEntriesByServerId($serverId);
    }

    private function deleteStatistics(int $serverId): void
    {
        $statisticsController = new StatisticController();
        $statisticsController->deleteStatisticsByServerId($serverId);
    }

    private function deleteBanners(int $serverId): void
    {
        $bannerController = new BannerController();
        $bannerController->deleteBannersByServerId($serverId);
    }
}
