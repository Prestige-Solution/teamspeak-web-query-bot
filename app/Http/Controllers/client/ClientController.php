<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\UpdateAfkWorkerSettingsRequest;
use App\Http\Requests\Client\UpdatePoliceWorkerSettingsRequest;
use App\Http\Requests\Client\ViewUpsertAfkWorkerRequest;
use App\Http\Requests\Client\ViewUpsertPoliceWorkerRequest;
use App\Models\ts3Bot\ts3Channel;
use App\Models\ts3Bot\ts3ServerGroup;
use App\Models\ts3BotWorkers\ts3BotWorkerAfk;
use App\Models\ts3BotWorkers\ts3BotWorkerPolice;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

class ClientController extends Controller
{
    public function viewUpsertPoliceWorker(ViewUpsertPoliceWorkerRequest $request): View|Factory|RedirectResponse|Application
    {
        $policeWorkerSetting = ts3BotWorkerPolice::query()
            ->where('server_id','=',$request->validated('ServerID'))
            ->first();

        $serverGroups = ts3ServerGroup::query()
            ->where('server_id','=', $request->validated('ServerID'))
            ->where('type','=',1)
            ->get(['sgid','name']);

        return view('backend.bot-worker.client.police-worker-settings')->with([
            'serverID'=>$request->validated('ServerID'),
            'policeWorker'=>$policeWorkerSetting,
            'serverGroups'=>$serverGroups,
        ]);
    }

    public function viewUpsertAfkWorker(ViewUpsertAfkWorkerRequest $request): View|Factory|RedirectResponse|Application
    {
        //Vorlagengruppen => Typ 0 //Normale Gruppen => Typ 1 //ServerQuery Gruppen => Typ 2
        $serverGroups = ts3ServerGroup::query()
            ->where('server_id','=', $request->validated('ServerID'))
            ->where('type','=',1)
            ->get(['sgid','name']);

        $channels = ts3Channel::query()
            ->where('server_id','=',$request->validated('ServerID'))
            ->get(['cid','channel_name']);

        $afkWorkerAfkChannel = ts3BotWorkerAfk::query()
            ->where('server_id','=',$request->validated('ServerID'))
            ->first('afk_channel_cid');

        $afkWorkerOptions = ts3BotWorkerAfk::query()
            ->where('server_id','=',$request->validated('ServerID'))
            ->first([
                'active',
                'max_client_idle_time',
                'afk_kicker_max_idle_time',
                'afk_kicker_slots_online',
                'afk_kicker_active',
            ]);

        $afkExcludedServerGroups = ts3BotWorkerAfk::query()
            ->where('server_id','=',$request->validated('ServerID'))
            ->get(['excluded_servergroup']);

        return view('backend.bot-worker.client.afk-worker-settings')->with([
            'serverGroups'=>$serverGroups,
            'channels'=>$channels,
            'server_id'=>$request->validated('ServerID'),
            'afkChannel'=>$afkWorkerAfkChannel->afk_channel_cid ?? 0,
            'excludedServerGroups'=>$afkExcludedServerGroups ?? NULL,
            'max_client_idle_time'=>$afkWorkerOptions->max_client_idle_time ?? 0,
            'active'=>$afkWorkerOptions->active ?? 0,
            'afk_kicker_max_idle_time'=>$afkWorkerOptions->afk_kicker_max_idle_time ?? 0,
            'afk_kicker_slots_online'=>$afkWorkerOptions->afk_kicker_slots_online ?? 0,
            'afk_kicker_active'=>$afkWorkerOptions->afk_kicker_active ?? 0,
        ]);
    }

    public function updatePoliceWorkerSettings(UpdatePoliceWorkerSettingsRequest $request): RedirectResponse
    {
        //create forget date // 1 = day 2 = weeks
        $forgetDate = match ($request->validated('PoliceDeleteClientsTimeType')) {
            1 => Carbon::now()->addDays($request->validated('PoliceDeleteClientsOfflineTime')),
            2 => Carbon::now()->addWeekdays($request->validated('PoliceDeleteClientsOfflineTime')),
            3 => Carbon::now()->addMonths($request->validated('PoliceDeleteClientsOfflineTime')),
            default => Carbon::now(),
        };

        //set settings
        ts3BotWorkerPolice::query()
            ->where('server_id','=',$request->validated('ServerID'))
            ->update([
                'discord_webhook_active'=>$request->validated('DiscordWebhookActive'),
                'check_bot_alive_active'=>$request->validated('PoliceCheckBotAlive'),
                'vpn_protection_active'=>$request->validated('PoliceVpnProtection'),
                'discord_webhook'=>Crypt::encryptString($request->validated('DiscordWebhookUrl')),
                'allow_sgid_vpn'=>$request->validated('AllowVpnForServerGroup'),
                'channel_auto_update'=>$request->validated('PoliceAutoupdateChannels'),
                'client_forget_offline_time'=>$request->validated('PoliceDeleteClientsOfflineTime'),
                'client_forget_type'=>$request->validated('PoliceDeleteClientsTimeType'),
                'client_forget_after'=>$forgetDate,
                'client_forget_active'=>$request->validated('PoliceDeleteClientsActive'),
                'bad_name_protection_active'=>$request->validated('PoliceBadNames'),
                'bad_name_protection_global_list_active'=>$request->validated('PoliceBadNamesGlobalList'),
            ]);

        return redirect()->back()->with(['success'=>'Einstellungen erfolgreich aktualisiert.']);
    }

    public function updateAfkWorkerSettings(UpdateAfkWorkerSettingsRequest $request): RedirectResponse
    {
        if($request->validated('AfkWorkerActive') == 1)
        {
            $active = true;
        }else
        {
            $active = false;
        }

        //clear afk worker table
        ts3BotWorkerAfk::query()->where('server_id','=',$request->validated('ServerID'))->delete();
        //create afk worker config
        $excludedServerGroups = collect($request->validated('ServerGroupSgid'));
        //exists excluded Groups
        if ($excludedServerGroups->count() != 0)
        {
            foreach ($excludedServerGroups as $excludedServerGroup)
            {
                ts3BotWorkerAfk::query()->create([
                    'server_id'=>$request->validated('ServerID'),
                    'max_client_idle_time'=>$request->validated('MaxIdleTimeSec')*1000*60,
                    'afk_channel_cid'=>$request->validated('AfkChannelCid'),
                    'excluded_servergroup'=>$excludedServerGroup,
                    'active'=>$active,
                    'afk_kicker_max_idle_time'=>$request->validated('AfkKickClientIdleTime')*1000*60,
                    'afk_kicker_slots_online'=>$request->validated('AfkKickClientSlotsOnline'),
                    'afk_kicker_active'=>$request->validated('AfkKickClientsActive'),
                ]);
            }
        }

        //exclude // Vorlagengruppen => Typ 0 //Normale Gruppen => Typ 1 //ServerQuery Gruppen => Typ 2
        $excludeStandardServerGroups = ts3ServerGroup::query()
            ->where('server_id','=', $request->validated('ServerID'))
            ->where('type','=',0)
            ->orWhere('type','=',2)
            ->get(['sgid','name']);

        foreach ($excludeStandardServerGroups as $excludeStandardServerGroup)
        {
            ts3BotWorkerAfk::query()->create([
                'server_id'=>$request->validated('ServerID'),
                'max_client_idle_time'=>$request->validated('MaxIdleTimeSec')*1000*60,
                'afk_channel_cid'=>$request->validated('AfkChannelCid'),
                'excluded_servergroup'=>$excludeStandardServerGroup->sgid,
                'active'=>$active,
                'afk_kicker_max_idle_time'=>$request->validated('AfkKickClientIdleTime')*1000*60,
                'afk_kicker_slots_online'=>$request->validated('AfkKickClientSlotsOnline'),
                'afk_kicker_active'=>$request->validated('AfkKickClientsActive'),
            ]);
        }

        return redirect()->route('worker.view.createOrUpdateAfkWorker');
    }
}
