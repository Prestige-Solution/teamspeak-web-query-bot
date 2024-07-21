@extends('template')

@section('site-title')
    Bot Control Center
@endsection

@section('custom-css')
    <link href="{{asset('css/font-awesome/css/fontawesome.css')}}" rel="stylesheet">
    <link href="{{asset('css/font-awesome/css/brands.css')}}" rel="stylesheet">
    <link href="{{asset('css/font-awesome/css/solid.css')}}" rel="stylesheet">
@endsection

@section('content')
    <div class="container mt-2">
        <form method="post" action="{{route('serverConfig.update.switchDefaultServer')}}">
            @csrf
            <div class="row mb-2">
                <div class="col-lg-8">
                    <h2 class="fs-3 fw-bold">Control Center | {{$server->server_name}} |
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
                    </h2>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex justify-content-end">
                        <select class="form-select" name="ServerID" id="ServerID">
                            @foreach($availableServers as $availableServer)
                                <option value="{{$availableServer->id}}" @if($server->id == $availableServer->id) selected @endif>{{$availableServer->server_name}}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-primary ms-2"><i class="fa-solid fa-repeat"></i></button>
                    </div>
                </div>
            </div>
        </form>
        <hr>
        @include('form-components/alertCustomError')
        @include('form-components/successCustom')
        @if($server->bot_update == true)
            <div class="alert alert-danger ms-2" role="alert">
                Es finden derzeit Wartungsarbeiten statt. Der Bot ist derzeit nicht verfügbar.
            </div>
        @endif
        <div class="row mb-2">
            <div class="col-lg-4 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <div class="card-body">
                        <h5 class="card-title fs-3 fw-bold">
                            <i class="fa-solid fa-gears mb-3"></i> Bot Control
                        </h5>
                        <p class="card-text">
                            Starte oder Stoppe deinen Ts3 Bot
                        </p>
                    </div>
                    <div class="row">
                        <div class="col-lg-auto">
                            @if($server->bot_update == false)
                                @if($server->ts3_start_stop == 0 && $server->bot_status_id == 3)
                                <form method="post" action="{{Route('ts3.start.ts3Bot')}}">
                                    @csrf
                                    <button class="btn btn-link" name="ServerID" value="{{$server->id}}"><i class="fa-solid fa-circle-play fa-2x text-success"></i></button>
                                </form>
                                @elseif($server->ts3_start_stop == 0 && $server->bot_status_id != 3)
                                    <div class="alert alert-warning ms-2" role="alert">
                                        Der Bot wird gestoppt. Bitte warten.
                                    </div>
                                @else
                                    <form method="post" action="{{Route('ts3.stop.ts3Bot')}}">
                                        @csrf
                                        <button class="btn btn-link" name="ServerID" value="{{$server->id}}"><i class="fa-solid fa-circle-stop fa-2x text-danger"></i></button>
                                    </form>
                                @endif
                            @else
                                <div class="alert alert-warning ms-2" role="alert">
                                    Wartungsmodus aktiv
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <a href="{{Route('worker.view.upsertPoliceWorker')}}" class="text-decoration-none text-dark">
                        <div class="card-body">
                            <h5 class="card-title fs-3 fw-bold">
                                <i class="fa-solid fa-gear mb-3"></i> Bot Einstellungen
                            </h5>
                            <p class="card-text">
                                Überwache deinen Server und lass dich bei Fehlern benachrichtigen. Nutze weitere Funktionen, um deinen Server sauber zu halten.
                            </p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <a href="{{Route('banner.view.listBanner')}}" class="text-decoration-none text-dark">
                    <div class="card-body">
                        <h5 class="card-title fs-3 fw-bold">
                            <i class="fa-solid fa-image mb-3"></i> Banner Creator
                        </h5>
                        <p class="card-text">
                            Erstelle deine individuellen Banner und lass ihn dir dynamisch in deinem Teamspeak anzeigen.
                        </p>
                    </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="row mb-2 mt-3">
            <div class="col-lg-12">
                <h2 class="fs-3 fw-bold">Funktionen</h2>
            </div>
        </div>
        <hr>
        <div class="row mb-3">
            <div class="col-lg-4 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <a href="{{Route('channel.view.listChannel')}}" class="text-decoration-none text-dark">
                        <div class="card-body">
                            <h5 class="card-title fs-3 fw-bold">
                                <i class="fa-solid fa-bars-staggered mb-3"></i> Channel Creator
                            </h5>
                            <p class="card-text">
                                Lass den Bot Channels überwachen und bei Bedarf neue erstellen.
                            </p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <a href="{{Route('worker.view.listChannelRemover')}}" class="text-decoration-none text-dark">
                    <div class="card-body">
                        <h5 class="card-title fs-3 fw-bold">
                            <i class="fa-solid fa-user-ninja mb-3"></i> Channel Remover
                        </h5>
                        <p class="card-text">
                            Halte deinen Server sauber, indem du ungenutzte Sub-Channels einfach automatisch entfernen lässt.
                        </p>
                    </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 d-flex align-items-stretch">
                <div class="card flex-fill">
                    <a href="{{Route('worker.view.createOrUpdateAfkWorker')}}" class="text-decoration-none text-dark">
                        <div class="card-body">
                            <h5 class="card-title fs-3 fw-bold">
                                <i class="fa-solid fa-user-ninja mb-3"></i> AFK Einstellungen
                            </h5>
                            <p class="card-text">
                                Verschiebe inaktive Clients in einen AFK Channel.
                            </p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
