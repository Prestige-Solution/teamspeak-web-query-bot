@extends('template')

@section('site-title')
    Bot Logs | {{ config('app.project') }}
@endsection

@section('content')
    <div class="container mt-3">
        <div class="row mb-2">
            <div class="col-lg-12">
                <h1 class="fs-3 fw-bold">Bot Logs | {{ \Illuminate\Support\Facades\Auth::user()->rel_server->server_name }}</h1>
            </div>
        </div>
        <hr>
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
@endsection
