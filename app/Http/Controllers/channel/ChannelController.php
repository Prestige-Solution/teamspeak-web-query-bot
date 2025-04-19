<?php

namespace App\Http\Controllers\channel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Channel\DeleteChannelJobRequest;
use App\Http\Requests\Channel\UpsertChannelJobRequest;
use App\Http\Requests\Channel\ViewListChannelJobsRequest;
use App\Models\ts3Bot\ts3Channel;
use App\Models\ts3Bot\ts3ChannelGroup;
use App\Models\ts3Bot\ts3ServerGroup;
use App\Models\ts3BotEvents\ts3BotAction;
use App\Models\ts3BotEvents\ts3BotActionUser;
use App\Models\ts3BotEvents\ts3BotEvent;
use App\Models\ts3BotWorkers\ts3BotWorkerChannelsCreate;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ChannelController extends Controller
{
    public function viewChannelJobs(ViewListChannelJobsRequest $request): View|Factory|RedirectResponse|Application
    {
        $jobs = ts3BotWorkerChannelsCreate::query()
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

        $tsChannels = ts3Channel::query()
            ->where('server_id', '=', $request->validated('server_id'))
            ->orderBy('channel_order')
            ->get(['id', 'channel_name', 'cid', 'pid', 'channel_order']);

        $tsChannelTemplates = ts3Channel::query()
            ->where('server_id', '=', $request->validated('server_id'))
            ->whereNot('channel_name', 'like', '%spacer%')
            ->orderBy('cid')
            ->get(['id', 'channel_name', 'cid', 'pid', 'channel_order']);

        $botEvents = ts3BotEvent::query()->where('cat_job_type', '=', 2)->get();
        $botActions = ts3BotAction::query()->where('type_id', '=', 1)->get();
        $botActionUsers = ts3BotActionUser::query()->get();
        $tsServerGroups = ts3ServerGroup::query()->where('server_id', '=', $request->validated('server_id'))
            ->where('type', '=', 1)->get(['sgid', 'name']);

        $tsChannelGroups = ts3ChannelGroup::query()->where('server_id', '=', $request->validated('server_id'))
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
        ts3BotWorkerChannelsCreate::query()->updateOrCreate(
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
                'is_active'=>$request->validated('is_active'),
            ]
        );

        return redirect()->route('channel.view.channelJobs')->with(['success' => 'The job was successfully updated']);
    }

    public function deleteChannelJob(DeleteChannelJobRequest $request): RedirectResponse
    {
        //delete entry
        ts3BotWorkerChannelsCreate::query()
            ->where('id', '=', $request->validated('id'))
            ->where('server_id', '=', $request->validated('server_id'))
            ->delete();

        return redirect()->route('channel.view.channelJobs')->with(['success'=>'The job was successfully deleted']);
    }
}
