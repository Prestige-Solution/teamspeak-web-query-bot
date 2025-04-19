@extends('template')

@section('site-title')
    AFK Settings | {{ config('app.project') }}
@endsection

@section('content')
<div class="container mt-3 mb-3">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="fs-3 fw-bold">AFK Settings | {{ \Illuminate\Support\Facades\Auth::user()->rel_server->server_name }}</h1>
        </div>
    </div>
    <hr>
    <form method="post" action="{{Route('worker.update.afkWorker')}}">
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
                            <p class="fs-4 fw-bold m-0"><i class="fa-solid fa-mug-saucer"></i> AFK Mover</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="is_afk_active">Status</label>
                            <select class="form-select" name="is_afk_active" id="is_afk_active">
                                <option value="0" @if($is_afk_active == 0) selected @endif>Inactive</option>
                                <option value="1" @if($is_afk_active == 1) selected @endif>Active</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="max_client_idle_time">Inactivity since (minute/s)</label>
                            <input class="form-control" type="number" name="max_client_idle_time" id="max_client_idle_time" min="1" max="50000" @if($max_client_idle_time == 0) value="1" @else value="{{$max_client_idle_time / (1000*60)}}" @endif>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="afk_channel_cid">AFK Channel</label>
                            <select class="form-select" id="afk_channel_cid" name="afk_channel_cid">
                                <option selected disabled>Please choose</option>
                                @foreach($tsChannels->where('pid','=',0) as $tsChannel)
                                    <option value="{{$tsChannel->cid}}" @if($afkChannelCid == $tsChannel->cid) selected @endif>- {{$tsChannel->channel_name}}</option>
                                    @foreach($tsChannels->where('pid','=',$tsChannel->cid) as $tsChannelPID)
                                        <option value="{{$tsChannelPID->cid}}" @if($afkChannelCid == $tsChannelPID->cid) selected @endif>-- {{$tsChannelPID->channel_name}}</option>
                                    @endforeach
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
                            <label class="form-label fw-bold" for="is_afk_kicker_active">Status</label>
                            <select class="form-select" name="is_afk_kicker_active" id="is_afk_kicker_active">
                                <option value="0" @if($is_afk_kicker_active == false) selected @endif>Inactive</option>
                                <option value="1" @if($is_afk_kicker_active == true) selected @endif>Active</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="afk_kicker_max_idle_time">Inactivity since (minute/s)</label>
                            <input class="form-control" type="number" name="afk_kicker_max_idle_time" id="afk_kicker_max_idle_time" min="1" max="9999" @if($afk_kicker_max_idle_time == 0) value="1" @else value="{{$afk_kicker_max_idle_time / (1000*60)}}" @endif>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="afk_kicker_slots_online">Used server slots</label>
                            <input class="form-control" type="number" name="afk_kicker_slots_online" id="afk_kicker_slots_online" min="0" max="3000" @if($afk_kicker_slots_online == 0) value="0" @else value="{{$afk_kicker_slots_online}}" @endif>
                            <div class="form-text">Determine from which number of clients on the server idle clients should be kicked (0 = Always)</div>
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
                            <p class="fs-4 fw-bold m-0"><i class="fa-solid fa-user-group"></i> Exclude server groups</p>
                        </div>
                        <div class="mb-3">
                            <select class="form-select" size="10" multiple name="excluded_servergroup[]" id="excluded_servergroup" aria-label="excluded_servergroup">
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
