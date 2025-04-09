@extends('template')

@section('site-title')
    Server | {{config('app.name')}}
@endsection

@section('content')
<div class="container mt-3">
    <div class="row mb-2">
        <div class="col-lg-8">
            <h2 class="fs-3 fw-bold">Server</h2>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-lg-3">
            <div class="card">
                <div class="card-body">
                <button type="button"
                        class="btn btn-link m-0 p-0 text-start text-decoration-none text-dark fw-bold fs-5"
                        data-bs-toggle="modal"
                        data-bs-target="#CreateServer">
                    <i class="fa-solid fa-circle-plus"></i> Add Server
                </button>
                </div>
            </div>
        </div>
    </div>
    <hr>
    @include('form-components.alertCustomError')
    @include('form-components.successCustom')
    @if($servers->count() === 0)
    <div>
        <div class="alert alert-primary" role="alert">
            No servers could be found.
        </div>
    </div>
    <hr>
    @else
    <div class="row row-cols-1 row-cols-lg-2 g-2 mb-2">
        @foreach($servers as $server)
            <div class="col d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <h3 class="card-title fw-bold">{{ $server->server_name }}</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="fw-bold">Status:</span>
                                <span class="text-secondary">
                                    @if($server->rel_bot_status->id === \App\Models\category\catBotStatus::$running)
                                        <span class="badge text-bg-success">{{$server->rel_bot_status->status_name}}</span>
                                    @endif
                                    @if($server->rel_bot_status->id === \App\Models\category\catBotStatus::$reconnect)
                                        <span class="badge text-bg-warning">{{$server->rel_bot_status->status_name}}</span>
                                    @endif
                                    @if($server->rel_bot_status->id === \App\Models\category\catBotStatus::$stopped)
                                        <span class="badge text-bg-danger">{{$server->rel_bot_status->status_name}}</span>
                                    @endif
                                    @if($server->rel_bot_status->id === \App\Models\category\catBotStatus::$failed)
                                        <span class="badge text-bg-danger">{{$server->rel_bot_status->status_name}}</span>
                                @endif
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="fw-bold">Query admin:</span>
                                <span class="text-secondary">{{ $server->qa_name }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="fw-bold">Host:</span>
                                <span class="text-secondary">{{ $server->server_ip }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="fw-bold">Server port:</span>
                                <span class="text-secondary">{{ $server->server_port }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="fw-bold">Connection mode:</span>
                                @if($server->mode === \App\Models\ts3Bot\ts3ServerConfig::TS3ConnectModeRAW)
                                    <span class="badge text-bg-warning">RAW</span>
                                @elseif($server->mode === \App\Models\ts3Bot\ts3ServerConfig::TS3ConnectModeSSH)
                                    <span class="badge text-bg-success">SSH</span>
                                @endif
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="fw-bold">Query port:</span>
                                @if($server->server_query_port === null)
                                    @if($server->mode === \App\Models\ts3Bot\ts3ServerConfig::TS3ConnectModeSSH)
                                        <span class="text-secondary">10022</span>
                                    @else
                                        <span class="text-secondary">10011</span>
                                    @endif
                                @else
                                    <span class="text-secondary">{{ $server->server_query_port }}</span>
                                @endif
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="fw-bold">Edit mode:</span>
                                @if($server->id === \Illuminate\Support\Facades\Auth::user()->default_server_id && \Illuminate\Support\Facades\Auth::user()->default_server_id !== 0)
                                    <span class="badge text-bg-success">Active</span>
                                @else
                                    <form class="m-0 p-0" method="post" action="{{route('serverConfig.update.switchDefaultServer')}}">
                                        @csrf
                                        <button class="btn btn-link m-0 p-0" type="submit" name="server_id" value="{{$server->id}}"><span class="badge text-bg-warning">Switch to Active</span></button>
                                    </form>
                                @endif
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="fw-bold">Bot nickname:</span>
                                <span class="text-secondary">{{ $server->qa_nickname }}</span>
                            </li>
                        </ul>
                        <hr>
                        <div>
                            <p class="fw-bold">Description:</p>
                            <p>{{ $server->description }}</p>
                        </div>
                    </div>
                    <div class="card-footer text-body-secondary">
                        <div class="row">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#UpdateServer{{$server->id}}"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                                </div>
                                <div>
                                    <button class="btn btn-danger me-2" type="button" data-bs-toggle="modal" data-bs-target="#ServerReInit{{$server->id}}"><i class="fa-solid fa-recycle"></i> Reinitialize</button>
                                    <button class="btn btn-danger" type="button" data-bs-toggle="modal" data-bs-target="#ServerDelete{{$server->id}}"><i class="fa-solid fa-trash"></i> Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif
</div>
@include('backend.server.inc.create-server')

@foreach($servers as $server)
    @include('backend.server.inc.update-server', ['server'=>$server])
@endforeach

@foreach($servers as $server)
    @include('backend.server.inc.init-server', ['server'=>$server])
@endforeach

@foreach($servers as $server)
    @include('backend.server.inc.delete-server', ['server'=>$server])
@endforeach
@endsection
