@extends('template')

@section('site-title')
    Serverliste | {{config('app.name')}}
@endsection

@section('custom-css')
    <link href="{{asset('css/font-awesome/css/fontawesome.css')}}" rel="stylesheet">
    <link href="{{asset('css/font-awesome/css/brands.css')}}" rel="stylesheet">
    <link href="{{asset('css/font-awesome/css/solid.css')}}" rel="stylesheet">
@endsection

@section('content')
    <div class="container mt-3">
        <div class="row mb-2">
            <div class="col-lg-8">
                <h2 class="fs-3 fw-bold">Serverliste</h2>
            </div>
        </div>
        <hr>
        <form method="get" action="#">
            <div class="row">
                <div class="col-lg-3">
                    <div class="card">
                        <a href="{{Route('serverConfig.view.createServer')}}" class="text-decoration-none text-dark">
                            <div class="card-body">
                                <h5 class="card-title fs-5 fw-bold">
                                    <i class="fa-solid fa-circle-plus"></i> Server Hinzufügen
                                </h5>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </form>
        <hr>
        @include('form-components.alertCustomError')
        @include('form-components.successCustom')
        @if($servers->count() === 0)
        <div>
            <div class="alert alert-primary" role="alert">
                Es wurden noch keine Server hinzugefügt. <a href="{{Route('serverConfig.view.createServer')}}">Jetzt einen Server hinzufügen.</a>
            </div>
        </div>
        @else
        <div class="row">
            <div class="col-lg-12">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col"></th>
                        <th scope="col">Servername</th>
                        <th scope="col">Query Admin</th>
                        <th scope="col">IP Address</th>
                        <th scope="col">Server Port</th>
                        <th scope="col">Query Port</th>
                        <th scope="col">Mode</th>
                        <th scope="col">Status</th>
                        <th scope="col"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($servers as $server)
                        <tr>
                            <td class="col-lg-1">
                                @if($server->default == true)
                                    <span class="badge text-bg-success">Active</span>
                                @else
                                    <form class="m-0 p-0" method="post" action="{{route('serverConfig.update.switchDefaultServer')}}">
                                        @csrf
                                        <button class="btn btn-link m-0 p-0 me-2" type="submit" name="ServerID" value="{{$server->id}}"><span class="badge text-bg-warning">Switch</span></button>
                                    </form>
                                @endif
                            </td>
                            <td class="col-lg-3">{{$server->server_name}}</td>
                            <td class="col-lg-2">{{$server->qa_name}}</td>
                            <td class="col-lg-1">{{$server->server_ip}}</td>
                            <td class="col-lg-1">{{$server->server_port}}</td>
                            <td class="col-lg-1">{{$server->server_query_port}}</td>
                            <td class="col-lg-1">
                                @if($server->mode == 1)
                                    <span class="badge text-bg-warning">RAW</span>
                                @else
                                    <span class="badge text-bg-success">SSH</span>
                                @endif
                            </td>
                            <td class="col-lg-1">
                                @if($server->rel_bot_status->id == 1)
                                    <span class="badge text-bg-success">{{$server->rel_bot_status->status_name}}</span>
                                @endif
                                @if($server->rel_bot_status->id == 2)
                                    <span class="badge text-bg-warning">{{$server->rel_bot_status->status_name}}</span>
                                @endif
                                @if($server->rel_bot_status->id == 3)
                                    <span class="badge text-bg-danger">{{$server->rel_bot_status->status_name}}</span>
                                @endif
                                @if($server->rel_bot_status->id == 4)
                                    <span class="badge text-bg-danger">{{$server->rel_bot_status->status_name}}</span>
                                @endif
                            </td>
                            <td class="col-lg-1">
                                <div class="d-flex justify-content-end">
                                    <form class="m-0 p-0" method="post" action="{{route('serverConfig.view.updateServer')}}">
                                        @csrf
                                        <button class="btn btn-link text-primary m-0 p-0 me-2" type="submit" name="ServerID" value="{{$server->id}}"><i class="fa-solid fa-pen-to-square"></i></button>
                                    </form>
                                    <form class="m-0 p-0">
                                        <button class="btn btn-link text-danger m-0 p-0 me-2" type="button" data-bs-toggle="modal" data-bs-target="#ServerReInit{{$server->id}}"><i class="fa-solid fa-recycle"></i></button>
                                    </form>
                                    <form class="m-0 p-0">
                                        <button class="btn btn-link text-danger m-0 p-0 me-2" type="button" data-bs-toggle="modal" data-bs-target="#ServerDelete{{$server->id}}"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
@endsection

@foreach($servers as $server)
    @include('backend.server.inc.inc-server-init', ['server'=>$server])
@endforeach

@foreach($servers as $server)
    @include('backend.server.inc.inc-server-delete', ['server'=>$server])
@endforeach
