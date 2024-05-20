@extends('template')

@section('site-title')
    AFK Einstellungen | PS-Bot
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
            <h1 class="fs-3 fw-bold">AFK Einstellungen</h1>
        </div>
    </div>
    <hr>
    @include('form-components.alertCustomError')
    <form method="post" action="{{Route('worker.update.afkWorker',['server_id'=>$server_id])}}">
        @csrf
        <div class="row mb-3">
            <div class="col-lg-2 d-grid">
                <button class="btn btn-primary" name="ServerID" value="{{$server_id}}">Speichern</button>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <div class="card-body">
                        <div class="row mb-3">
                            <p class="fs-4 fw-bold m-0"><i class="fa-solid fa-mug-saucer"></i> AFK Mover</p>
                        </div>
                        <div class="mb-3">
                            <label class="col-lg-2 col-form-label fw-bold" for="AfkWorkerActive">Aktiv</label>
                            <select class="form-select" name="AfkWorkerActive" id="AfkWorkerActive">
                                <option value="0" @if($active == 0) selected @endif>Inaktiv</option>
                                <option value="1" @if($active == 1) selected @endif>Aktiv</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="MaxIdleTimeSec">Untätigkeit sei (Min.)</label>
                            <input class="form-control" type="number" name="MaxIdleTimeSec" id="MaxIdleTimeSec" min="1" max="50000" @if($max_client_idle_time == 0) value="1" @else value="{{$max_client_idle_time / (1000*60)}}" @endif>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="AfkChannelCid">AFK Channel</label>
                            <select class="form-select" id="AfkChannelCid" name="AfkChannelCid">
                                <option selected disabled>Bitte wählen</option>
                                @foreach($channels as $channel)
                                    <option value="{{$channel->cid}}" @if($afkChannel == $channel->cid) selected @endif>{{$channel->channel_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <div class="card-body">
                        <div class="row mb-3">
                            <p class="fs-4 fw-bold m-0"><i class="fa-solid fa-user-xmark"></i> AFK Kicker</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="AfkKickClientsActive">Aktiv</label>
                            <select class="form-select" name="AfkKickClientsActive" id="AfkKickClientsActive">
                                <option value="0" @if($afk_kicker_active == 0) selected @endif>Inaktiv</option>
                                <option value="1" @if($afk_kicker_active == 1) selected @endif>Aktiv</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="AfkKickClientIdleTime">Untätig seit (min)</label>
                            <input class="form-control" type="number" name="AfkKickClientIdleTime" id="AfkKickClientIdleTime" min="1" max="9999" @if($afk_kicker_max_idle_time == 0) value="1" @else value="{{$afk_kicker_max_idle_time / (1000*60)}}" @endif>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="AfkKickClientSlotsOnline">Belegte Serverslots</label>
                            <input class="form-control" type="number" name="AfkKickClientSlotsOnline" id="AfkKickClientSlotsOnline" min="0" max="3000" @if($afk_kicker_slots_online == 0) value="0" @else value="{{$afk_kicker_slots_online}}" @endif>
                            <div class="form-text">Bestimme ab welcher Anzahl an Clients auf dem Server untätige gekickt werden sollen (0 = Immer)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-lg-12 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <div class="card-body">
                        <div class="mb-3">
                            <p class="fs-4 fw-bold m-0"><i class="fa-solid fa-user-group"></i> Servergruppen ausschließen</p>
                        </div>
                        <div class="mb-3">
                            <select class="form-select" size="10" multiple name="ServerGroupSgid[]" id="ServerGroupSgid" aria-label="ServerGroupSgid">
                                @foreach($serverGroups as $serverGroup)
                                    <option value="{{$serverGroup->sgid}}" @if($excludedServerGroups != null) @if($excludedServerGroups->where('excluded_servergroup','=',$serverGroup->sgid)->count() != 0) selected @endif @endif >{{$serverGroup->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection