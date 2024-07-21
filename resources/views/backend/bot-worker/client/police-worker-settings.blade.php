@extends('template')

@section('site-title')
    Bot Einstellungen | PS-Bot
@endsection

@section('custom-css')
    <link href="{{asset('css/font-awesome/css/fontawesome.css')}}" rel="stylesheet">
    <link href="{{asset('css/font-awesome/css/brands.css')}}" rel="stylesheet">
    <link href="{{asset('css/font-awesome/css/solid.css')}}" rel="stylesheet">
@endsection

@section('content')
<div class="container mt-3 mb-3">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="fs-3 fw-bold">Bot Einstellungen</h1>
        </div>
    </div>
    <hr>
    @include('form-components.alertCustomError')
    @include('form-components.successCustom')
    <form method="post" action="{{Route('worker.create.updatePoliceWorkerSettings')}}">
        @csrf
        <div class="row mb-3 mt-3">
            <div class="col-lg-2 d-grid">
                <button class="btn btn-primary" name="ServerID" value="{{$serverID}}">Speichern</button>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <div class="card-body">
                        <div class="row mb-3">
                            <p class="fs-4 fw-bold"><i class="fa-solid fa-bell"></i> Discord Benachrichtigungen</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="DiscordWebhookActive">Status:</label>
                            <select class="form-select" name="DiscordWebhookActive" id="DiscordWebhookActive" aria-label="DiscordWebhookActive">
                                <option value="1" @if($policeWorker->discord_webhook_active == 1) selected @endif>Aktiv</option>
                                <option value="0" @if($policeWorker->discord_webhook_active == 0) selected @endif>Inaktiv</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="DiscordWebhookUrl">Webhook URL</label>
                            <input class="form-control" type="url" name="DiscordWebhookUrl" id="DiscordWebhookUrl" placeholder="https://discord.de/webhook" aria-label="DiscordWebhookUrl"
                            @if($policeWorker->discord_webhook != NULL) value="{{\Illuminate\Support\Facades\Crypt::decryptString($policeWorker->discord_webhook)}}" @endif>
                            <div class="form-text">
                                Gib hier deinen Webhook Link ein, welchen du via Discord erstellt hast.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <div class="card-body">
                        <div class="row mb-3">
                            <p class="fs-4 fw-bold"><i class="fa-solid fa-lock"></i> VPN Protection</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="PoliceVpnProtection">Status</label>
                            <select class="form-select" name="PoliceVpnProtection" id="PoliceVpnProtection">
                                <option value="1" @if($policeWorker->vpn_protection_active == 1) selected @endif>Aktiv</option>
                                <option value="0" @if($policeWorker->vpn_protection_active == 0) selected @endif>Inaktiv</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="AllowVpnForServerGroup">Gruppe ausschließen</label>
                            <select class="form-select" name="AllowVpnForServerGroup" id="AllowVpnForServerGroup" aria-label="AllowVpnForServerGroup">
                                @foreach($serverGroups as $serverGroup)
                                    <option value="{{$serverGroup->sgid}}" @if($policeWorker->allow_sgid_vpn == $serverGroup->sgid) selected @endif>{{$serverGroup->name}}</option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Wähle die Servergruppe welche nicht überprüft werden soll
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-lg-6 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <div class="card-body">
                        <div class="row mb-3">
                            <p class="fs-4 fw-bold"><i class="fa-solid fa-signal"></i> Bot online Prüfung</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="PoliceCheckBotAlive">Status</label>
                            <select class="form-select" name="PoliceCheckBotAlive" id="PoliceCheckBotAlive">
                                <option value="1" @if($policeWorker->check_bot_alive_active == 1) selected @endif>Aktiv</option>
                                <option value="0" @if($policeWorker->check_bot_alive_active == 0) selected @endif>Inaktiv</option>
                            </select>
                            <div class="col-lg-12">
                                <p class="form-text col-form-label">Prüft regelmäßig, ob der Bot online ist. Andernfalls wirst du via Discord benachrichtigt.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <div class="card-body">
                        <div class="row mb-3">
                            <p class="fs-4 fw-bold"><i class="fa-solid fa-user-slash"></i> Clients permanent löschen</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="PoliceDeleteClientsActive">Status</label>
                            <select class="form-select" name="PoliceDeleteClientsActive" id="PoliceDeleteClientsActive">
                                <option value="1" @if($policeWorker->client_forget_active == true) selected @endif>Aktiv</option>
                                <option value="0" @if($policeWorker->client_forget_active == false) selected @endif>Inaktiv</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="PoliceDeleteClientsOfflineTime">Client offline seit:</label>
                            <input class="form-control mb-2" type="number" name="PoliceDeleteClientsOfflineTime" id="PoliceDeleteClientsOfflineTime" min="1" value="{{$policeWorker->client_forget_offline_time}}">
                            <select class="form-select" name="PoliceDeleteClientsTimeType" id="PoliceDeleteClientsTimeType" aria-label="PoliceDeleteClientsTimeType">
                                <option value="1" @if($policeWorker->client_forget_type == 1) selected @endif>Tag/e</option>
                                <option value="2" @if($policeWorker->client_forget_type == 2) selected @endif>Woche/n</option>
                                <option value="3" @if($policeWorker->client_forget_type == 3) selected @endif>Monat/e</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-lg-6 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <div class="card-body">
                        <div class="row mb-3">
                            <p class="fs-4 fw-bold"><i class="fa-solid fa-ban"></i> Bad Name Protection</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="PoliceBadNames">Status</label>
                            <select class="form-select" name="PoliceBadNames" id="PoliceBadNames">
                                <option value="1" @if($policeWorker->bad_name_protection_active == 1) selected @endif >Aktiv</option>
                                <option value="0" @if($policeWorker->bad_name_protection_active == 0) selected @endif >Inaktiv</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="PoliceBadNamesGlobalList">Globale Liste einschließen</label>
                            <select class="form-select" name="PoliceBadNamesGlobalList" id="PoliceBadNamesGlobalList">
                                <option value="1" @if($policeWorker->bad_name_protection_global_list_active == 1) selected @endif >Ja</option>
                                <option value="0" @if($policeWorker->bad_name_protection_global_list_active == 0) selected @endif >Nein</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <div class="card-body">
                        <div class="row mb-3">
                            <p class="fs-4 fw-bold"><i class="fa-solid fa-rotate"></i> Channels automatisch synchronisieren</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="PoliceAutoupdateChannels">Status</label>
                            <select class="form-select" name="PoliceAutoupdateChannels" id="PoliceAutoupdateChannels">
                                <option value="1" @if($policeWorker->channel_auto_update == 1) selected @endif>Aktiv</option>
                                <option value="0" @if($policeWorker->channel_auto_update == 0) selected @endif>Inaktiv</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
