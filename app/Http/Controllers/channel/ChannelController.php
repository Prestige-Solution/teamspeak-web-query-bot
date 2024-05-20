<?php

namespace App\Http\Controllers\channel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Channel\CreateChannelJobRequest;
use App\Http\Requests\Channel\CreateChannelRemoverRequest;
use App\Http\Requests\Channel\DeleteChannelJobRequest;
use App\Http\Requests\Channel\DeleteChannelRemoverRequest;
use App\Http\Requests\Channel\ViewListChannelJobsRequest;
use App\Http\Requests\Channel\ViewListChannelRemoverRequest;
use App\Http\Requests\Channel\ViewUpsertChannelJobsRequest;
use App\Http\Requests\Channel\ViewUpsertChannelRemoverRequest;
use App\Models\ts3Bot\ts3Channel;
use App\Models\ts3Bot\ts3ChannelGroup;
use App\Models\ts3Bot\ts3ServerGroup;
use App\Models\ts3BotEvents\ts3BotAction;
use App\Models\ts3BotEvents\ts3BotActionUser;
use App\Models\ts3BotEvents\ts3BotEvent;
use App\Models\ts3BotJobs\ts3BotJobCreateChannels;
use App\Models\ts3BotWorkers\ts3BotWorkerChannelRemover;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ChannelController extends Controller
{
    public function viewListChannelJobs(ViewListChannelJobsRequest $request): View|Factory|RedirectResponse|Application
    {
        $jobs = ts3BotJobCreateChannels::query()
            ->with([
                'rel_servers',
                'rel_types',
                'rel_actions',
                'rel_action_users',
                'rel_channels',
            ])
            ->where('server_id','=',$request->validated('server_id'))
            ->get();

        return view('backend.bot-worker.channel.list-channel-job')->with([
            'jobs'=>$jobs,
            'serverID'=>$request->validated('server_id'),
        ]);
    }

    public function viewUpsertChannelJobs(ViewUpsertChannelJobsRequest $request): View|Factory|RedirectResponse|Application
    {
        $tsChannels = ts3Channel::query()
            ->where('server_id','=',$request->validated('server_id'))
            ->orderBy('channel_order')
            ->get(['id','channel_name','cid','pid','channel_order']);

        $tsChannelTemplates = ts3Channel::query()
            ->where('server_id','=',$request->validated('server_id'))
            ->whereNot('channel_name','like','%spacer%')
            ->orderBy('cid')
            ->get(['id','channel_name','cid']);

        $botEvents = ts3BotEvent::query()->where('cat_job_type','=',2)->get();
        $botActions = ts3BotAction::query()->where('type_id','=',1)->get();
        $botActionUsers = ts3BotActionUser::query()->get();
        $tsServerGroups = ts3ServerGroup::query()->where('server_id','=',$request->validated('server_id'))->where('type','=', 1)->get(['sgid','name']);
        $ts3ChannelGroups = ts3ChannelGroup::query()->where('server_id','=',$request->validated('server_id'))->where('type','=',1)->get(['id','cgid','name']);

        if ($request->has('update') == 1)
        {
            $ts3BotJob = ts3BotJobCreateChannels::query()
                ->where('id','=',$request->validated('job_id'))
                ->where('server_id','=',$request->validated('server_id'))
                ->first();

            return view('backend.bot-worker.channel.create-or-update-channel-job')->with([
                'tsChannels'=>$tsChannels,
                'tsChannelTemplates'=>$tsChannelTemplates,
                'botEvents'=>$botEvents,
                'botActions'=>$botActions,
                'botActionUsers'=>$botActionUsers,
                'tsServerGroups'=>$tsServerGroups,
                'serverID'=>$request->validated('server_id'),
                'tsChannelGroups'=>$ts3ChannelGroups,
                'ts3BotJob'=>$ts3BotJob,
                'update'=>1,
            ]);
        }else
        {
            return view('backend.bot-worker.channel.create-or-update-channel-job')->with([
                'tsChannels'=>$tsChannels,
                'tsChannelTemplates'=>$tsChannelTemplates,
                'botEvents'=>$botEvents,
                'botActions'=>$botActions,
                'botActionUsers'=>$botActionUsers,
                'tsServerGroups'=>$tsServerGroups,
                'serverID'=>$request->validated('server_id'),
                'tsChannelGroups'=>$ts3ChannelGroups,
            ]);
        }
    }

    public function viewListChannelRemover(ViewListChannelRemoverRequest $request): View|Factory|RedirectResponse|Application
    {
        //get channels
        $channelLists = ts3BotWorkerChannelRemover::query()->with(
            'rel_ts3ChannelsRemover',
        )
            ->where('server_id','=',$request->validated('server_id'))
            ->get(['id','channel_cid','channel_max_seconds_empty','channel_max_time_format','active','server_id']);

        return view('backend.bot-worker.channel.list-channel-remover-worker')->with([
            'serverID'=>$request->validated('server_id'),
            'channelLists'=>$channelLists,
        ]);
    }

    public function viewCreateOrUpdateChannelRemoverChannel(ViewUpsertChannelRemoverRequest $request): View|Factory|RedirectResponse|Application
    {
        //get channels
        $tsChannels = ts3Channel::query()
            ->where('server_id','=',$request->validated('server_id'))
            ->orderBy('cid')
            ->get(['id','channel_name','cid','pid','channel_order']);

        if($request->has('update'))
        {
            //get settings
            $channelRemoverSetting = ts3BotWorkerChannelRemover::query()
                ->where('server_id','=',$request->validated('server_id'))
                ->where('id','=',$request->validated('remover_id'))
                ->first();

            return view('backend.bot-worker.channel.create-or-update-channel-remover-worker')->with([
                'ts3Channels'=>$tsChannels,
                'serverID'=>$request->validated('server_id'),
                'channelRemoverSetting'=>$channelRemoverSetting,
                'update'=>1,
            ]);

        }else
        {
            return view('backend.bot-worker.channel.create-or-update-channel-remover-worker')->with([
                'ts3Channels'=>$tsChannels,
                'serverID'=>$request->validated('server_id'),
            ]);
        }
    }

    public function createChannelJob(CreateChannelJobRequest $request): RedirectResponse
    {
        ts3BotJobCreateChannels::query()->updateOrCreate(
            [
                'server_id'=>$request->validated('ServerID'),
                'type_id'=>1,
                'on_cid'=>$request->validated('ChannelTarget'),
                'on_event'=>$request->validated('ChannelEvent'),
            ],
            [
                'action_id'=>$request->validated('ChannelAction'),
                'action_min_clients'=>$request->validated('ChannelActionMinClientCount'),
                'create_max_channels'=>$request->validated('MaxChannels'),
                'action_user_id'=>$request->validated('ChannelActionUserInChannel'),
                'channel_cgid'=>$request->validated('ChannelActionUserInChannelGroup'),
                'channel_template_id'=>$request->validated('ChannelTemplate'),
                'notify_message_server_group'=>$request->validated('NotifyServerGroupBool'),
                'notify_message_server_group_sgid'=>$request->validated('NotifyServerGroupSgid'),
                'notify_message_server_group_message'=>$request->validated('NotifyServerGroupMessage'),
            ]
        );

        return redirect()->route('channel.view.channelList');
    }

    public function createChannelRemover(CreateChannelRemoverRequest $request): RedirectResponse
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

        return redirect()->route('worker.view.listChannelRemover',['server_id'=>$request->validated('ServerID')]);
    }

    public function deleteChannelJob(DeleteChannelJobRequest $request)
    {
        //delete entry
        ts3BotJobCreateChannels::query()
            ->where('id','=',$request->validated('DeleteID'))
            ->delete();

        //TODO Create redirect to view
//        return redirect()->route('channel.view.channelList',['server_id'=>$request->validated('ServerID')]);
    }

    public function deleteChannelRemover(DeleteChannelRemoverRequest $request)
    {
        //get serverID
        ts3BotWorkerChannelRemover::query()->where('id','=',$request->validated('DeleteID'))->first(['server_id'])->server_id;
        //delete entry
        ts3BotWorkerChannelRemover::query()->where('id','=',$request->validated('DeleteID'))->delete();

        //TODO Create redirect to view
//        return redirect()->route('worker.view.listChannelRemover',['server_id'=>$request->validated('ServerID')]);
    }
}
