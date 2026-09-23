<?php

namespace App\Http\Controllers\channel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Channel\DeleteChannelJobRequest;
use App\Http\Requests\Channel\UpsertChannelJobRequest;
use App\Http\Requests\Channel\ViewListChannelJobsRequest;
use App\Models\tsBot\tsChannel;
use App\Models\tsBot\tsChannelGroup;
use App\Models\tsBot\tsServerGroup;
use App\Models\tsBotEvents\tsBotAction;
use App\Models\tsBotEvents\tsBotActionUser;
use App\Models\tsBotEvents\tsBotEvent;
use App\Models\tsBotWorkers\tsBotWorkerChannelsCreate;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;

class ChannelController extends Controller
{
    public function viewChannelJobs(ViewListChannelJobsRequest $request): View|Factory|RedirectResponse|Application
    {
        $jobs = tsBotWorkerChannelsCreate::query()
            ->with([
                'rel_servers',
                'rel_types',
                'rel_actions',
                'rel_action_users',
                'rel_channels',
                'rel_bot_event',
                'rel_cgid',
                'rel_template_channel',
                'rel_sgid',
                'rel_pid',
            ])
            ->where('server_id', '=', $request->validated('server_id'))
            ->orderBy('on_cid')
            ->get();

        $channels = tsChannel::query()
            ->where('server_id', '=', $request->validated('server_id'))
            ->get(['id', 'channel_name', 'cid', 'pid', 'channel_order']);

        $tsChannels = $this->buildChannelOptions($channels->toBase());

        $templateChannels = tsChannel::query()
            ->where('server_id', '=', $request->validated('server_id'))
            ->whereNot('channel_name', 'like', '%spacer%')
            ->get(['id', 'channel_name', 'cid', 'pid', 'channel_order']);

        $tsChannelTemplates = $this->buildChannelOptions($templateChannels->toBase());

        $botEvents = tsBotEvent::query()->where('cat_job_type', '=', 2)->get();
        $botActions = tsBotAction::query()->where('type_id', '=', 1)->get();
        $botActionUsers = tsBotActionUser::query()->get();
        $tsServerGroups = tsServerGroup::query()->where('server_id', '=', $request->validated('server_id'))
            ->where('type', '=', 1)->get(['sgid', 'name']);

        $tsChannelGroups = tsChannelGroup::query()->where('server_id', '=', $request->validated('server_id'))
            ->where('type', '=', 1)->get(['id', 'cgid', 'name']);

        return view('backend.jobs.channel-creator.channel-creator-job-list')->with([
            'jobs'=>$jobs,
            'tsChannels'=>$tsChannels,
            'tsChannelTemplates'=>$tsChannelTemplates,
            'botEvents'=>$botEvents,
            'botActions'=>$botActions,
            'botActionUsers'=>$botActionUsers,
            'tsServerGroups'=>$tsServerGroups,
            'tsChannelGroups'=>$tsChannelGroups,
        ]);
    }

    public function upsertChannelJob(UpsertChannelJobRequest $request): RedirectResponse
    {
        tsBotWorkerChannelsCreate::query()->updateOrCreate(
            [
                'server_id'=>$request->validated('server_id'),
                'type_id'=>1,
                'on_cid'=>$request->validated('on_cid'),
                'on_event'=>$request->validated('on_event'),
            ],
            [
                'action_id'=>$request->validated('action_id'),
                'action_min_clients'=>$request->validated('action_min_clients'),
                'create_max_channels'=>$request->validated('create_max_channels'),
                'action_user_id'=>$request->validated('action_user_id'),
                'channel_cgid'=>$request->validated('channel_cgid'),
                'channel_template_cid'=>$request->validated('channel_template_cid'),
                'is_notify_message_server_group'=>$request->validated('is_notify_message_server_group'),
                'notify_message_server_group_sgid'=>$request->validated('notify_message_server_group_sgid'),
                'notify_message_server_group_message'=>$request->validated('notify_message_server_group_message'),
                'notify_option'=>$request->validated('notify_option'),
                'is_active'=>$request->validated('is_active'),
            ]
        );

        return redirect()->route('channel.view.channelJobs')->with(['success' => 'The job was successfully updated']);
    }

    public function deleteChannelJob(DeleteChannelJobRequest $request): RedirectResponse
    {
        $this->deleteChannelCreateJobsById($request->validated('server_id'), $request->validated('id'));

        return redirect()->route('channel.view.channelJobs')->with(['success'=>'The job was successfully deleted']);
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

    private function deleteChannelCreateJobsById(int $serverId, int $id): void
    {
        tsBotWorkerChannelsCreate::query()
            ->where('id', '=', $id)
            ->where('server_id', '=', $serverId)
            ->delete();
    }

    public function deleteChannelCreateJobsByServerId(int $serverId): void
    {
        tsBotWorkerChannelsCreate::query()->where('server_id', '=', $serverId)->delete();
    }
}
