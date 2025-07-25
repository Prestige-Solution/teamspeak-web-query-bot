@extends('template')

@section('site-title')
    Bot Control Center | {{ config('app.project') }}
@endsection

@section('content')
    @if(empty($server) || \Illuminate\Support\Facades\Auth::user()->default_server_id === 0)
    <div class="container mt-2">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="fw-bold">Control Center</h2>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="alert alert-primary" role="alert">
                No server has been found. you can manage your servers here <a href="{{route('serverConfig.view.serverList')}}">Server</a>.
            </div>
        </div>
    </div>
    @else
    <div class="container mt-2">
            <div class="row mb-2">
                <div class="col-lg-8">
                    <h2 class="fs-3 fw-bold">Control Center | {{ \Illuminate\Support\Facades\Auth::user()->rel_server->server_name }}</h2>
                    <h5>
                        @if($server->bot_status_id == 1)
                            <span class="badge bg-success">{{$server->rel_bot_status->status_name}}</span>
                        @endif
                        @if($server->bot_status_id == 2)
                            <span class="badge bg-warning">{{$server->rel_bot_status->status_name}}</span>
                        @endif
                        @if($server->bot_status_id == 3)
                            <span class="badge bg-danger">{{$server->rel_bot_status->status_name}}</span>
                        @endif
                        @if($server->bot_status_id == 4)
                            <span class="badge bg-danger">{{$server->rel_bot_status->status_name}}</span>
                        @endif
                    </h5>
                </div>
                <div class="col-lg-4">
                    <form method="post" action="{{route('serverConfig.update.switchDefaultServer')}}">
                        @csrf
                        <div class="d-flex justify-content-end">
                            <select class="form-select" name="server_id" id="server_id">
                                @foreach($availableServers as $availableServer)
                                    <option value="{{$availableServer->id}}" @if($server->id == $availableServer->id) selected @endif>{{$availableServer->server_name}}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary ms-2"><i class="fa-solid fa-repeat"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        <hr>
        @include('form-components/alertCustomError')
        @include('form-components/successCustom')
        @if($server->is_bot_update == true)
            <div class="alert alert-danger ms-2" role="alert">
                Maintenance work is currently in progress. The bot is currently not available.
            </div>
        @endif
        <div class="row mb-2 mt-3">
            <div class="col-lg-12">
                <h2 class="fs-3 fw-bold">Functions</h2>
            </div>
        </div>
        <hr>
        <div class="row mb-3">
            <div class="col-lg-4 mb-2 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <div class="card-body">
                        <h5 class="card-title fs-3 fw-bold">
                            <i class="fa-solid fa-gears mb-3"></i> Bot Control
                        </h5>
                        <p class="card-text">
                            Start or stop the Bot
                        </p>
                    </div>
                    <div class="row">
                        <div class="col-lg-auto">
                            @if($server->is_bot_update == false)
                                @if($server->is_ts3_start == 0 && $server->bot_status_id == 3)
                                    <form class="mb-2 ms-2" method="post" action="{{Route('ts3.start.ts3Bot')}}">
                                        @csrf
                                        <button type="submit" class="btn btn-success"><i class="fa-solid fa-circle-play"></i> Start</button>
                                    </form>
                                @elseif($server->is_ts3_start == 0 && $server->bot_status_id != 3)
                                    <form class="mb-2 ms-2" method="post" action="{{Route('ts3.start.ts3Bot')}}">
                                        @csrf
                                        <button type="submit" class="btn btn-warning"><i class="fa-solid fa-circle-play"></i> Restart</button>
                                    </form>
                                @else
                                    <form class="mb-2 ms-2" method="post" action="{{Route('ts3.stop.ts3Bot')}}">
                                        @csrf
                                        <button type="submit" class="btn btn-danger"><i class="fa-solid fa-circle-stop"></i> Stop</button>
                                    </form>
                                @endif
                            @else
                                <div class="alert alert-warning mb-2 ms-2" role="alert">
                                    Maintenance mode active
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-2 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <a href="{{Route('channel.view.channelJobs')}}" class="text-decoration-none text-dark">
                        <div class="card-body">
                            <h5 class="card-title fs-3 fw-bold">
                                <i class="fa-solid fa-bars-staggered mb-3"></i> Channel Creator
                            </h5>
                            <p class="card-text">
                                With the Channel Creator you can dynamically create channels and define client actions.
                            </p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 mb-2 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <a href="{{Route('channel.view.listChannelRemover')}}" class="text-decoration-none text-dark">
                    <div class="card-body">
                        <h5 class="card-title fs-3 fw-bold">
                            <i class="fa-solid fa-list-check mb-3"></i> Channel Remover
                        </h5>
                        <p class="card-text">
                            You can use the Channel Remover to automatically delete unused sub-channels
                        </p>
                    </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 mb-2 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <a href="{{Route('banner.view.listBanner')}}" class="text-decoration-none text-dark">
                        <div class="card-body">
                            <h5 class="card-title fs-3 fw-bold">
                                <i class="fa-solid fa-image mb-3"></i> Banner Creator
                            </h5>
                            <p class="card-text">
                                Create your individual banners and display them dynamically in your Teamspeak
                            </p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="row mb-2 mt-3">
            <div class="col-lg-12">
                <h2 class="fs-3 fw-bold">Settings</h2>
            </div>
        </div>
        <hr>
        <div class="row mb-2">
            <div class="col-lg-4 mb-2 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <a href="{{Route('worker.view.upsertPoliceWorker')}}" class="text-decoration-none text-dark">
                        <div class="card-body">
                            <h5 class="card-title fs-3 fw-bold">
                                <i class="fa-solid fa-gear mb-3"></i> Police Settings
                            </h5>
                            <p class="card-text">
                                Monitor your server and be notified or errors. Use other functions to keep your server clean.
                            </p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 mb-2 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <a href="{{Route('worker.view.createOrUpdateAfkWorker')}}" class="text-decoration-none text-dark">
                        <div class="card-body">
                            <h5 class="card-title fs-3 fw-bold">
                                <i class="fa-solid fa-user-minus mb-3"></i> AFK Settings
                            </h5>
                            <p class="card-text">
                                Move inactive Clients to a specific channel or kick them from the server
                            </p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 mb-2 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <a href="{{Route('worker.view.badNames')}}" class="text-decoration-none text-dark">
                        <div class="card-body">
                            <h5 class="card-title fs-3 fw-bold">
                                <i class="fa-solid fa-users-slash mb-3"></i> Bad Nickname Settings
                            </h5>
                            <p class="card-text">
                                Define and prevent Bad Names for Channel- and Usernames
                            </p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection
