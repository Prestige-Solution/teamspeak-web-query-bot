@extends('template')

@section('site-title')
    Bot Settings | {{config('app.project')}}
@endsection

@section('content')
<div class="container mt-3 mb-3">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="fs-3 fw-bold">Bot Settings | {{ \Illuminate\Support\Facades\Auth::user()->rel_server->server_name }}</h1>
        </div>
    </div>
    <hr>
    <form method="post" action="{{Route('worker.create.updatePoliceWorkerSettings')}}">
        @csrf
        <div class="col-lg-3">
            <div class="card">
                <div class="card-body">
                    <button type="submit"
                            class="btn btn-link m-0 p-0 text-start text-decoration-none text-dark fw-bold fs-5">
                        <i class="fa-solid fa-floppy-disk"></i> Submit
                    </button>
                </div>
            </div>
        </div>
        <hr>
        @include('form-components.alertCustomError')
        @include('form-components.successCustom')
        <div class="row">
            <div class="col-lg-6 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <div class="card-body">
                        <div class="row mb-3">
                            <p class="fs-4 fw-bold"><i class="fa-solid fa-bell"></i> Discord Notification</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="is_discord_webhook_active">Status:</label>
                            <select class="form-select" name="is_discord_webhook_active" id="is_discord_webhook_active" aria-label="DiscordWebhookActive">
                                <option value="1" @if($policeWorker->is_discord_webhook_active == true) selected @endif>Active</option>
                                <option value="0" @if($policeWorker->is_discord_webhook_active == false) selected @endif>Inactive</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="discord_webhook_url">Webhook URL</label>
                            <input class="form-control" type="url" name="discord_webhook_url" id="discord_webhook_url" placeholder="https://discord.de/webhook" aria-label="DiscordWebhookUrl"
                            @if($policeWorker->discord_webhook_url != NULL) value="{{\Illuminate\Support\Facades\Crypt::decryptString($policeWorker->discord_webhook_url)}}" @endif>
                            <div class="form-text">
                                Enter your webhook link here, which you created via Discord
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
                            <label class="form-label fw-bold" for="is_vpn_protection_active">Status:</label>
                            <select class="form-select" name="is_vpn_protection_active" id="is_vpn_protection_active">
                                <option value="1" @if($policeWorker->is_vpn_protection_active == true) selected @endif>Active</option>
                                <option value="0" @if($policeWorker->is_vpn_protection_active == false) selected @endif>Inactive</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="vpn_protection_api_register_mail">E-Mail:</label>
                            <input class="form-control" type="email" name="vpn_protection_api_register_mail" id="vpn_protection_api_register_mail" value="{{$policeWorker->vpn_protection_api_register_mail}}">
                            <div class="form-text">
                                If you want to use this feature then take a look at <a href="http://www.getipintel.net/free-proxy-vpn-tor-detection-api/" target="_blank">GetIPIntel.net Api Documentation</a>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="allow_sgid_vpn">Exclude Group</label>
                            <select class="form-select" name="allow_sgid_vpn" id="allow_sgid_vpn" aria-label="allow_sgid_vpn">
                                @foreach($serverGroups as $serverGroup)
                                    <option value="{{$serverGroup->sgid}}" @if($policeWorker->allow_sgid_vpn == $serverGroup->sgid) selected @endif>{{$serverGroup->name}}</option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Select the server group which should not be checked
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
                            <p class="fs-4 fw-bold"><i class="fa-solid fa-signal"></i> Check bot online status</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="is_check_bot_alive_active">Status:</label>
                            <select class="form-select" name="is_check_bot_alive_active" id="is_check_bot_alive_active">
                                <option value="1" @if($policeWorker->is_check_bot_alive_active == true) selected @endif>Active</option>
                                <option value="0" @if($policeWorker->is_check_bot_alive_active == false) selected @endif>Inactive</option>
                            </select>
                            <div class="col-lg-12">
                                <p class="form-text col-form-label">Check regularly whether the bot is online. If not, you will be notified via a notify channel</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <div class="card-body">
                        <div class="row mb-3">
                            <p class="fs-4 fw-bold"><i class="fa-solid fa-user-slash"></i> Permanently delete clients</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="is_client_forget_active">Status:</label>
                            <select class="form-select" name="is_client_forget_active" id="is_client_forget_active">
                                <option value="1" @if($policeWorker->is_client_forget_active == true) selected @endif>Active</option>
                                <option value="0" @if($policeWorker->is_client_forget_active == false) selected @endif>Inactive</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="client_forget_offline_time">Client offline since:</label>
                            <input class="form-control mb-2" type="number" name="client_forget_offline_time" id="client_forget_offline_time" min="1" value="{{$policeWorker->client_forget_offline_time}}">
                            <select class="form-select" name="client_forget_time_type" id="client_forget_time_type" aria-label="client_forget_time_type">
                                <option value="1" @if($policeWorker->client_forget_time_type == 1) selected @endif>day/s</option>
                                <option value="2" @if($policeWorker->client_forget_time_type == 2) selected @endif>week/s</option>
                                <option value="3" @if($policeWorker->client_forget_time_type == 3) selected @endif>month/s</option>
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
                            <label class="form-label fw-bold" for="is_bad_name_protection_active">Status:</label>
                            <select class="form-select" name="is_bad_name_protection_active" id="is_bad_name_protection_active">
                                <option value="1" @if($policeWorker->is_bad_name_protection_active == true) selected @endif >Active</option>
                                <option value="0" @if($policeWorker->is_bad_name_protection_active == false) selected @endif >Inactive</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="is_bad_name_protection_global_list_active">Include global list</label>
                            <select class="form-select" name="is_bad_name_protection_global_list_active" id="is_bad_name_protection_global_list_active">
                                <option value="1" @if($policeWorker->is_bad_name_protection_global_list_active == true) selected @endif >Yes</option>
                                <option value="0" @if($policeWorker->is_bad_name_protection_global_list_active == false) selected @endif >No</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <div class="card-body">
                        <div class="row mb-3">
                            <p class="fs-4 fw-bold"><i class="fa-solid fa-rotate"></i> Synchronize channels automatically</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="is_channel_auto_update_active">Status:</label>
                            <select class="form-select" name="is_channel_auto_update_active" id="is_channel_auto_update_active">
                                <option value="1" @if($policeWorker->is_channel_auto_update_active == true) selected @endif>Active</option>
                                <option value="0" @if($policeWorker->is_channel_auto_update_active == false) selected @endif>Inactive</option>
                            </select>
                            <div class="col-lg-12">
                                <p class="form-text col-form-label">Each channel action is updated in the backend</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
