@extends('template')

@section('site-title')
    Server hinzufügen
@endsection

@section('content')
    <div class="container">
        <div class="row mt-3">
            <div class="col-lg-12">
                <h1 class="fs-3 fw-bold">Server @isset($update)
                        bearbeiten
                    @else
                        hinzufügen
                    @endisset</h1>
            </div>
        </div>
        <hr>
        @include('form-components.alertCustomError')
        @include('form-components/successCustom')
        <form method="post" action="{{Route('backend.create.createOrUpdateServer')}}">
            @csrf
            <div class="row mb-2">
                <label class="col-lg-2 col-form-label fw-bold" for="ServerName">Servername</label>
                <div class="col-lg-6">
                    <input class="form-control" type="text" name="ServerName" id="ServerName" placeholder="Servername"
                           @isset($update) @if($update == 1) value="{{$server->server_name}}" @endif @endisset>
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-lg-2 col-form-label fw-bold" for="Ipv4">IPv4 Adresse</label>
                <div class="col-lg-6">
                    <input class="form-control" type="text" name="Ipv4" id="Ipv4" placeholder="10.10.10.10"
                           @isset($update) @if($update == 1) value="{{$server->ipv4}}" @endif @endisset>
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-lg-2 col-form-label fw-bold" for="QaName">Query Admin</label>
                <div class="col-lg-6">
                    <input class="form-control" type="text" name="QaName" id="QaName" placeholder="Query Admin Name"
                           @isset($update) @if($update == 1) value="{{$server->qa_name}}" @endif @endisset maxlength="11">
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-lg-2 col-form-label fw-bold" for="QaPW">Query Admin Passwort</label>
                <div class="col-lg-6">
                    <input class="form-control" type="password" name="QaPW" id="QaPW"
                           placeholder="Query Admin Password">
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-lg-2 col-form-label fw-bold" for="ServerQueryPort">Query Port</label>
                <div class="col-lg-6">
                    <input class="form-control" type="text" name="ServerQueryPort" id="ServerQueryPort"
                           placeholder="Default 10011"
                           @isset($update) @if($update == 1) value="{{$server->server_query_port}}" @endif @endisset>
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-lg-2 col-form-label fw-bold" for="ServerPort">Server Port</label>
                <div class="col-lg-6">
                    <input class="form-control" type="text" name="ServerPort" id="ServerPort" placeholder="Default 9987"
                           @isset($update) @if($update == 1) value="{{$server->server_port}}" @endif @endisset>
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-lg-2 col-form-label fw-bold" for="QueryNickname">Query Nickname</label>
                <div class="col-lg-6">
                    <input class="form-control" type="text" name="QueryNickname" id="QueryNickname"
                           placeholder="Bot Nickname"
                           @isset($update) @if($update == 1) value="{{$server->qa_nickname}}" @endif @endisset
                           maxlength="11">
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-lg-2 col-form-label fw-bold" for="Description">Beschreibung</label>
                <div class="col-lg-6">
                    @isset($update)
                        @if($update == 1)
                            <textarea class="form-control" name="Description" id="Description" rows="3"
                                      maxlength="255">{{$server->description}}</textarea>
                        @endif
                    @else
                        <textarea class="form-control" name="Description" id="Description" rows="3"
                                  maxlength="255"></textarea>
                    @endisset
                </div>
            </div>
            <hr>
            <div class="row mb-2">
                @isset($update)
                @if($update == 1)
                    <div class="col-lg-2 d-grid">
                        <button class="btn btn-primary" name="ServerID" value="{{$server->id}}">Speichern</button>
                    </div>
                @endif
                @else
                    <div class="col-lg-3 d-grid">
                        <button class="btn btn-primary" name="ServerID" value="0">Speichern und Initialisieren</button>
                    </div>
                @endisset
                @isset($update)
                    @if($update == 1)
                        <div class="col-lg-2">
                            <button class="btn btn-danger" name="ServerID" value="-1">Server neu initialisieren</button>
                        </div>
                    @endif
                @endisset
            </div>
        </form>
    </div>
@endsection
