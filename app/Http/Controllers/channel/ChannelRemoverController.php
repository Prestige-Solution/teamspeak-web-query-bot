<?php

namespace App\Http\Controllers\channel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Channel\CreateChannelRemoverRequest;
use App\Http\Requests\Channel\DeleteChannelRemoverRequest;
use App\Http\Requests\Channel\ViewListChannelRemoverRequest;
use App\Models\tsBot\tsChannel;
use App\Models\tsBotWorkers\tsBotWorkerChannelsRemove;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;

class ChannelRemoverController extends Controller
{
    public function viewChannelRemoverJobs(ViewListChannelRemoverRequest $request): View|Factory|RedirectResponse|Application
    {
        $jobs = tsBotWorkerChannelsRemove::query()
            ->with('rel_channels')
            ->where('server_id', '=', $request->validated('server_id'))
            ->orderBy('channel_cid')
            ->get();

        $channels = tsChannel::query()
            ->where('server_id', '=', $request->validated('server_id'))
            ->get(['id', 'channel_name', 'cid', 'pid', 'channel_order']);

        $tsChannels = $this->buildChannelOptions($channels->toBase());

        return view('backend.jobs.channel-remover.channel-remover-job-list')->with([
            'jobs'=>$jobs,
            'tsChannels'=>$tsChannels,
        ]);
    }

    public function upsertChannelRemoverJob(CreateChannelRemoverRequest $request): RedirectResponse
    {
        //get seconds
        $channelMaxSecondsEmpty = match ($request->validated('channel_max_time_format')) {
            'h' => $request->validated('channel_max_seconds_empty') * 60 * 60,
            'd' => $request->validated('channel_max_seconds_empty') * 24 * 60 * 60,
            default => $request->validated('channel_max_seconds_empty') * 60,
        };

        //store
        tsBotWorkerChannelsRemove::query()->updateOrCreate(
            [
                'server_id' => $request->validated('server_id'),
                'channel_cid' => $request->validated('channel_cid'),
            ],
            [
                'channel_max_seconds_empty' => $channelMaxSecondsEmpty,
                'channel_max_time_format' => $request->validated('channel_max_time_format'),
                'is_active' => $request->validated('is_active'),
            ]
        );

        return redirect()->route('channel.view.listChannelRemover')->with(['success' => 'The job was successfully updated']);
    }

    public function deleteChannelRemoverJob(DeleteChannelRemoverRequest $request): RedirectResponse
    {
        $this->deleteChannelRemoveJobsById($request->validated('server_id'), $request->validated('id'));

        return redirect()->route('channel.view.listChannelRemover')->with(['success' => 'The job was successfully deleted']);
    }

    private function buildChannelOptions(Collection $channels, int $pid = 0, int $level = 0): Collection
    {
        return $channels
            ->where('pid', $pid)
            ->sortBy('channel_order')
            ->flatMap(function (tsChannel $channel) use ($channels, $level): Collection {
                $channel->tree_channel_name = str_repeat('-', $level).$channel->channel_name;

                return collect([$channel])->merge(
                    $this->buildChannelOptions($channels, $channel->cid, $level + 1)
                );
            })
            ->values();
    }

    private function deleteChannelRemoveJobsById(int $serverId, int $id): void
    {
        tsBotWorkerChannelsRemove::query()
            ->where('id', '=', $id)
            ->where('server_id', '=', $serverId)
            ->delete();
    }

    public function deleteChannelRemoveJobsByServerId(int $serverId): void
    {
        tsBotWorkerChannelsRemove::query()->where('server_id', '=', $serverId)->delete();
    }
}
