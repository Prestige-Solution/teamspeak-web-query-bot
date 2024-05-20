@extends('template')

@section('site-title')
    Botliste
@endsection

@section('custom-css')
    <link href="{{asset('css/font-awesome/css/fontawesome.css')}}" rel="stylesheet">
    <link href="{{asset('css/font-awesome/css/brands.css')}}" rel="stylesheet">
    <link href="{{asset('css/font-awesome/css/solid.css')}}" rel="stylesheet">
@endsection

@section('content')
    <div class="container mt-3">
        <div class="row mb-2">
            <div class="col-lg-12">
                <h2 class="fs-3 fw-bold">Serverliste</h2>
            </div>
        </div>
        <hr>
        <form method="get" action="#">
            <div class="row">
                <div class="col-lg-3">
                    <div class="card">
                        <a href="{{Route('backend.view.createOrUpdateServer')}}" class="text-decoration-none text-dark">
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
                        <th scope="col">Aktion</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($servers as $server)
                        <tr>
                            <td class="col-lg-1"><span class="badge text-bg-success">Default</span></td>
                            <td class="col-lg-3">{{$server->server_name}}</td>
                            <td class="col-lg-2">{{$server->qa_name}}</td>
                            <td class="col-lg-1">{{$server->ipv4}}</td>
                            <td class="col-lg-1">{{$server->server_port}}</td>
                            <td class="col-lg-1">{{$server->server_query_port}}</td>
                            <td class="col-lg-1"><span class="badge text-bg-warning">RAW</span> <span class="badge text-bg-success">SSH</span></td>
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
                                <button class="btn btn-link text-primary m-0 p-0" type="button"><i class="fa-solid fa-pen-to-square"></i></button>
                                <button class="btn btn-link text-danger m-0 p-0" type="button"><i class="fa-solid fa-trash ms-2"></i></button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
