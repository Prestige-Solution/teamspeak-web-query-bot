<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\UpdateAfkWorkerSettingsRequest;
use App\Http\Requests\Client\UpdatePoliceWorkerSettingsRequest;
use App\Http\Requests\Client\ViewUpsertAfkWorkerRequest;
use App\Http\Requests\Client\ViewUpsertPoliceWorkerRequest;
use App\Models\tsBot\tsChannel;
use App\Models\tsBot\tsServerGroup;
use App\Models\tsBotWorkers\tsBotWorkerAfk;
use App\Models\tsBotWorkers\tsBotWorkerPolice;
use App\Models\tsBotWorkers\tsBotWorkerPoliceVpnProtection;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Crypt;

class ClientController extends Controller
{
    public function viewPoliceWorker(ViewUpsertPoliceWorkerRequest $request): View|Factory|RedirectResponse|Application
    {
        $policeWorkerSetting = tsBotWorkerPolice::query()
            ->where('server_id', '=', $request->validated('server_id'))
            ->first();

        $serverGroups = tsServerGroup::query()
            ->where('server_id', '=', $request->validated('server_id'))
            ->where('type', '=', 1)
            ->get(['sgid', 'name']);

        return view('backend.jobs.worker.police.worker-settings-police')->with([
            'policeWorker'=>$policeWorkerSetting,
            'serverGroups'=>$serverGroups,
        ]);
    }

    public function viewAfkWorker(ViewUpsertAfkWorkerRequest $request): View|Factory|RedirectResponse|Application
    {
        //Vorlagengruppen = Typ 0 //Normale Gruppen = Typ 1 //ServerQuery Gruppen = Typ 2
        $serverGroups = tsServerGroup::query()
            ->where('server_id', '=', $request->validated('server_id'))
            ->where('type', '=', 1)
            ->get(['sgid', 'name']);

        $tsChannels = tsChannel::query()
            ->where('server_id', '=', $request->validated('server_id'))
            ->orderBy('channel_order')
            ->get(['id', 'channel_name', 'cid', 'pid', 'channel_order']);

        $afkWorkerAfkChannel = tsBotWorkerAfk::query()
            ->where('server_id', '=', $request->validated('server_id'))
            ->first('afk_channel_cid');

        $afkWorkerOptions = tsBotWorkerAfk::query()
            ->where('server_id', '=', $request->validated('server_id'))
            ->first();

        $afkExcludedServerGroups = tsBotWorkerAfk::query()
            ->where('server_id', '=', $request->validated('server_id'))
            ->get(['excluded_servergroup']);

        return view('backend.jobs.worker.afk.worker-settings-afk')->with([
            'serverGroups'=>$serverGroups,
            'tsChannels'=>$tsChannels,
            'afkChannelCid'=>$afkWorkerAfkChannel->afk_channel_cid ?? 0,
            'excludedServerGroups'=>$afkExcludedServerGroups ?? null,
            'max_client_idle_time'=>$afkWorkerOptions->max_client_idle_time ?? 0,
            'is_afk_active'=>$afkWorkerOptions->is_afk_active ?? 0,
            'afk_kicker_max_idle_time'=>$afkWorkerOptions->afk_kicker_max_idle_time ?? 0,
            'afk_kicker_slots_online'=>$afkWorkerOptions->afk_kicker_slots_online ?? 0,
            'is_afk_kicker_active'=>$afkWorkerOptions->is_afk_kicker_active ?? false,
        ]);
    }

    public function updatePoliceWorkerSettings(UpdatePoliceWorkerSettingsRequest $request): RedirectResponse
    {
        tsBotWorkerPolice::query()
            ->where('server_id', '=', $request->validated('server_id'))
            ->update([
                'is_discord_webhook_active'=>$request->validated('is_discord_webhook_active'),
                'is_check_bot_alive_active'=>$request->validated('is_check_bot_alive_active'),
                'is_vpn_protection_active'=>$request->validated('is_vpn_protection_active'),
                'vpn_protection_api_register_mail'=>$request->validated('vpn_protection_api_register_mail'),
                'discord_webhook_url'=>Crypt::encryptString($request->validated('discord_webhook_url')),
                'allow_sgid_vpn'=>$request->validated('allow_sgid_vpn'),
                'is_bad_name_protection_active'=>$request->validated('is_bad_name_protection_active'),
                'is_bad_name_protection_global_list_active'=>$request->validated('is_bad_name_protection_global_list_active'),
            ]);

        return redirect()->route('worker.view.upsertPoliceWorker')->with(['success'=>'Settings successfully updated']);
    }

    public function updateAfkWorkerSettings(UpdateAfkWorkerSettingsRequest $request): RedirectResponse
    {
        //clear afk worker table
        tsBotWorkerAfk::query()->where('server_id', '=', $request->validated('server_id'))->delete();
        //create afk worker config
        $excludedServerGroups = collect($request->validated('excluded_servergroup'));
        //exists excluded Groups
        if ($excludedServerGroups->count() != 0) {
            foreach ($excludedServerGroups as $excludedServerGroup) {
                tsBotWorkerAfk::query()->create([
                    'server_id'=>$request->validated('server_id'),
                    'is_afk_active'=>$request->validated('is_afk_active'),
                    'max_client_idle_time'=>$request->validated('max_client_idle_time') * 1000 * 60,
                    'afk_channel_cid'=>$request->validated('afk_channel_cid') ?? 0,
                    'is_afk_kicker_active'=>$request->validated('is_afk_kicker_active'),
                    'afk_kicker_max_idle_time'=>$request->validated('afk_kicker_max_idle_time') * 1000 * 60,
                    'afk_kicker_slots_online'=>$request->validated('afk_kicker_slots_online'),
                    'excluded_servergroup'=>$excludedServerGroup,
                ]);
            }
        }

        //exclude // Vorlagengruppen > Typ 0 //Normale Gruppen > Typ 1 //ServerQuery Gruppen > Typ 2
        $excludeStandardServerGroups = tsServerGroup::query()
            ->where('server_id', '=', $request->validated('server_id'))
            ->where(function ($query){
                $query->where('type', '=', 0)
                    ->orWhere('type', '=', 2);
            })
            ->get(['sgid', 'name']);

        foreach ($excludeStandardServerGroups as $excludeStandardServerGroup) {
            tsBotWorkerAfk::query()->create([
                'server_id'=>$request->validated('server_id'),
                'is_afk_active'=>$request->validated('is_afk_active'),
                'max_client_idle_time'=>$request->validated('max_client_idle_time') * 1000 * 60,
                'afk_channel_cid'=>$request->validated('afk_channel_cid') ?? 0,
                'is_afk_kicker_active'=>$request->validated('is_afk_kicker_active'),
                'afk_kicker_max_idle_time'=>$request->validated('afk_kicker_max_idle_time') * 1000 * 60,
                'afk_kicker_slots_online'=>$request->validated('afk_kicker_slots_online'),
                'excluded_servergroup'=>$excludeStandardServerGroup->sgid,
            ]);
        }

        return redirect()->route('worker.view.createOrUpdateAfkWorker')->with(['success'=>'Settings successfully updated']);
    }

    public function deleteAfkWorkerSettingsByServerId(int $server_id): void
    {
        tsBotWorkerAfk::query()->where('server_id', '=', $server_id)->delete();
    }

    public function deletePoliceWorkerSettingsByServerId(int $server_id): void
    {
        tsBotWorkerPolice::query()->where('server_id', '=', $server_id)->delete();
    }

    public function deletePoliceVpnProtectionWorkerSettingsByServerId(int $server_id): void
    {
        tsBotWorkerPoliceVpnProtection::query()->where('server_id', '=', $server_id)->delete();
    }
}
