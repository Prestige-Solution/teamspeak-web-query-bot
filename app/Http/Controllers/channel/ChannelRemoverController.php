<?php

namespace App\Http\Controllers\channel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Channel\CreateChannelRemoverRequest;
use App\Http\Requests\Channel\DeleteChannelRemoverRequest;
use App\Http\Requests\Channel\ViewCreateChannelRemoverRequest;
use App\Http\Requests\Channel\ViewListChannelRemoverRequest;
use App\Http\Requests\Channel\ViewUpsertChannelRemoverRequest;
use App\Models\ts3Bot\ts3Channel;
use App\Models\ts3BotWorkers\ts3BotWorkerChannelRemover;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ChannelRemoverController extends Controller
{
    public function viewChannelRemover(ViewListChannelRemoverRequest $request): View|Factory|RedirectResponse|Application
    {
        //get channels
        $channelLists = ts3BotWorkerChannelRemover::query()->with(
            'rel_ts3ChannelsRemover',
        )
            ->where('server_id','=',$request->validated('ServerID'))
            ->get(['id','channel_cid','channel_max_seconds_empty','channel_max_time_format','active','server_id']);

        return view('backend.bot-worker.channel.channel-remover')->with([
            'serverID'=>$request->validated('ServerID'),
            'channelLists'=>$channelLists,
        ]);
    }

    public function viewCreateChannelRemover(ViewCreateChannelRemoverRequest $request): View|Factory|RedirectResponse|Application
    {
        //get channels
        $tsChannels = ts3Channel::query()
            ->where('server_id','=',$request->validated('ServerID'))
            ->orderBy('cid')
            ->get(['id','channel_name','cid','pid','channel_order']);

        return view('backend.bot-worker.channel.upsert-channel-remover')->with([
            'ts3Channels'=>$tsChannels,
            'serverID'=>$request->validated('ServerID'),
        ]);
    }

    public function viewUpsertChannelRemover(ViewUpsertChannelRemoverRequest $request): View|Factory|\Illuminate\Foundation\Application
    {
        //get channels
        $tsChannels = ts3Channel::query()
            ->where('server_id','=',$request->validated('ServerID'))
            ->orderBy('cid')
            ->get(['id','channel_name','cid','pid','channel_order']);

        //get settings
        $channelRemoverSetting = ts3BotWorkerChannelRemover::query()
            ->where('server_id','=',$request->validated('ServerID'))
            ->where('id','=',$request->validated('RemoveID'))
            ->first();

        return view('backend.bot-worker.channel.upsert-channel-remover')->with([
            'ts3Channels'=>$tsChannels,
            'serverID'=>$request->validated('ServerID'),
            'channelRemoverSetting'=>$channelRemoverSetting,
            'update'=>1,
        ]);

    }

    public function upsertChannelRemover(CreateChannelRemoverRequest $request): RedirectResponse
    {
        //get seconds
        $seconds = match ($request->validated('MaxIdleTimeFormat')) {
            'h' => $request->validated('MaxIdleTime') * 60 * 60,
            'd' => $request->validated('MaxIdleTime') * 24 * 60 * 60,
            default => $request->validated('MaxIdleTime') * 60,
        };

        //store
        ts3BotWorkerChannelRemover::query()->updateOrCreate(
            [
                'server_id'=>$request->validated('ServerID'),
                'channel_cid'=>$request->validated('ChannelCid'),
            ],
            [
                'channel_max_seconds_empty'=>$seconds,
                'channel_max_time_format'=>$request->validated('MaxIdleTimeFormat'),
                'active'=>$request->validated('ChannelRemoverActive'),
            ]
        );

        return redirect()->route('worker.view.listChannelRemover')->with(['success'=>'Erfolgreich aktualisiert.']);
    }

    public function deleteChannelRemover(DeleteChannelRemoverRequest $request): RedirectResponse
    {
        //delete entry
        ts3BotWorkerChannelRemover::query()->where('id','=',$request->validated('DeleteID'))->delete();

        return redirect()->route('worker.view.listChannelRemover')->with(['success'=>'Eintrag wurde gelöscht']);
    }
}
