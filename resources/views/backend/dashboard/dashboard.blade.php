@extends('template')

@section('site-title')
    Dashboard | {{config('app.name')}}
@endsection

@section('content')
    @if(empty($server) || \Illuminate\Support\Facades\Auth::user()->default_server_id === 0)
        <div class="container mt-2">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="fw-bold">Dashboard</h2>
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
        <div class="container mt-3">
            <div class="row mb-2">
                <div class="col-lg-8">
                    <h2 class="fs-3 fw-bold">Dashboard | {{ \Illuminate\Support\Facades\Auth::user()->rel_server->server_name }} | Last Scan: {{ \Illuminate\Support\Carbon::parse($stats->updated_at)->format('d.m.y - h:i:s') }}</h2>
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
            <div class="row mb-3">
                <div class="col-lg-4 mb-2 d-flex align-items-stretch">
                    <div class="card flex-fill">
                        <div class="card-body">
                            <h5 class="card-title fs-3 fw-bold">
                                <i class="fa-solid fa-server mb-3"></i> Virtual Server
                            </h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="fw-bold">Clients:</span>
                                    <span class="text-secondary">{{ $stats->virtualserver_clientsonline }} / {{ $stats->virtualserver_maxclients }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="fw-bold">Channels:</span>
                                    <span class="text-secondary">{{ $stats->virtualserver_channelsonline }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="fw-bold">Operating system:</span>
                                    <span class="text-secondary">{{ $stats->virtualserver_platform }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="fw-bold">Version:</span>
                                    <span class="text-secondary">{{ $stats->virtualserver_version }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="fw-bold">Uptime:</span>
                                    <span class="text-secondary">{{ $stats->virtualserver_uptime }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-2 d-flex align-items-stretch">
                    <div class="card flex-fill">
                        <div class="card-body">
                            <h5 class="card-title fs-3 fw-bold">
                                <i class="fa-solid fa-user-group mb-3"></i> Groups & Query
                            </h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="fw-bold">Query Users online (total):</span>
                                    <span class="text-secondary">{{ $stats->virtualserver_queryclientsonline }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="fw-bold">Servergroups (total):</span>
                                    <span class="text-secondary">{{ $stats->virtualserver_server_group_count }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="fw-bold">Channelgroups (total):</span>
                                    <span class="text-secondary">{{ $stats->virtualserver_channel_group_count }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="fw-bold">Banned Users (total):</span>
                                    <span class="text-secondary">{{ $stats->virtualserver_banlist_count }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-2 d-flex align-items-stretch">
                    <div class="card flex-fill">
                        <div class="card-body">
                            <h5 class="card-title fs-3 fw-bold">
                                <i class="fa-solid fa-ethernet mb-3"></i> Traffic
                            </h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="fw-bold">Package sent (KeepAlive):</span>
                                    <span class="text-secondary">{{ $stats->virtualserver_connection_bytes_sent_keepalive }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="fw-bold">Package Receive (KeepAlive):</span>
                                    <span class="text-secondary">{{ $stats->virtualserver_connection_bytes_received_keepalive }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="fw-bold">Packetloss (KeepAlive):</span>
                                    <span class="text-secondary">{{ $stats->virtualserver_total_packetloss_keepalive }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="fw-bold">Packetloss Speech (Total):</span>
                                    <span class="text-secondary">{{ $stats->virtualserver_total_packetloss_speech }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="fw-bold">Ping (total):</span>
                                    <span class="text-secondary">{{ $stats->virtualserver_total_ping }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row mb-2">
                <div class="col-lg-12">
                    <h2 class="fs-3 fw-bold">Logs</h2>
                </div>
            </div>
            <hr>
            <div class="row mb-2">
                <div class="col-lg-12">
                    @if($botLogs->count() === 0)
                        <div class="row">
                            <div class="alert alert-warning" role="alert">
                                No log entries have been found yet
                            </div>
                        </div>
                    @else
                        <div class="row">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th scope="col">Status</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Error</th>
                                    <th scope="col">Worker</th>
                                    <th scope="col">Timestamp</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($botLogs as $botLog)
                                    <tr>
                                        <td class="col-lg-1">
                                            @if($botLog->status_id == 1 || $botLog->status_id == 5)
                                                <span class="badge text-bg-success">{{$botLog->rel_bot_status()->first()->status_name}}</span>
                                            @endif
                                            @if($botLog->status_id == 2)
                                                <span class="badge text-bg-warning">{{$botLog->rel_bot_status()->first()->status_name}}</span>
                                            @endif
                                            @if($botLog->status_id == 3 || $botLog->status_id == 4)
                                                <span class="badge text-bg-danger">{{$botLog->rel_bot_status()->first()->status_name}}</span>
                                            @endif
                                        </td>
                                        <td class="col-lg-3">{{$botLog->description}}</td>
                                        <td class="col-lg-4">{{$botLog->error_message}}</td>
                                        <td class="col-lg-2">{{$botLog->worker}}</td>
                                        <td class="col-lg-2">{{date('d.m.Y - H:i.s',strtotime($botLog->created_at))}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endsection
