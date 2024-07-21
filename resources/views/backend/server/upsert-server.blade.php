@extends('template')

@section('site-title')
    Server hinzufügen
@endsection

@section('content')
    <div class="container">
        <div class="row mt-3">
            <div class="col-lg-12">
                <h1 class="fs-3 fw-bold">Server @isset($update)
                        {{$server->server_name}} bearbeiten
                    @else
                        hinzufügen
                    @endisset
                </h1>
            </div>
        </div>
        <hr>
        @include('form-components/alertCustomError')
        @include('form-components/successCustom')
        <form method="post" @if(isset($update)) action="{{Route('serverConfig.update.server')}}" @else action="{{Route('serverConfig.create.server')}}" @endif >
            @csrf
            <div class="row mb-2">
                <label class="col-lg-2 col-form-label fw-bold" for="ServerName">Servername</label>
                <div class="col-lg-10">
                    <input class="form-control" type="text" name="ServerName" id="ServerName" placeholder="Servername"
                           @isset($update) @if($update == 1) value="{{$server->server_name}}" @endif @endisset>
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-lg-2 col-form-label fw-bold" for="ServerIP">IP Adresse</label>
                <div class="col-lg-10">
                    <input class="form-control" type="text" name="ServerIP" id="ServerIP" placeholder="IPV4 or IPV6 Address"
                           @isset($update) @if($update == 1) value="{{$server->server_ip}}" @endif @endisset>
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-lg-2 col-form-label fw-bold" for="QaName">Query Admin</label>
                <div class="col-lg-10">
                    <input class="form-control" type="text" name="QaName" id="QaName" placeholder="Query Admin Name"
                           @isset($update) @if($update == 1) value="{{$server->qa_name}}" @endif @endisset maxlength="11">
                    <div class="form-text">
                        Die Verwendung des Accounts "serveradmin" ist nicht möglich. Erstelle hierzu einen separaten Query Account
                    </div>
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-lg-2 col-form-label fw-bold" for="QaPW">Query Admin Passwort</label>
                <div class="col-lg-10">
                    <input class="form-control" type="password" name="QaPW" id="QaPW"
                           placeholder="Query Admin Password">
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-lg-2 col-form-label fw-bold" for="ServerQueryPort">Query Port</label>
                <div class="col-lg-10">
                    <input class="form-control" type="text" name="ServerQueryPort" id="ServerQueryPort"
                           placeholder="Default 10011"
                           @isset($update) @if($update == 1) value="{{$server->server_query_port}}" @endif @endisset>
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-lg-2 col-form-label fw-bold" for="ServerPort">Server Port</label>
                <div class="col-lg-10">
                    <input class="form-control" type="text" name="ServerPort" id="ServerPort" placeholder="Default 9987"
                           @isset($update) @if($update == 1) value="{{$server->server_port}}" @endif @endisset>
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-lg-2 col-form-label fw-bold" for="ConMode">Modus</label>
                <div class="col-lg-10">
                    <select class="form-select" name="ConMode" id="ConMode">
                        <option value="{{\App\Models\ts3Bot\ts3ServerConfig::TS3ConnectModeSSH}}" @if(isset($update)) @if($server->mode == 2) selected @endif @else selected @endif>SSH</option>
                        <option value="{{\App\Models\ts3Bot\ts3ServerConfig::TS3ConnectModeRAW}}" @if(isset($update)) @if($server->mode == 1) selected @endif @endif>RAW</option>
                    </select>
                    <div class="form-text">
                        SSH = Default | RAW = Trouble with SSH
                    </div>
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-lg-2 col-form-label fw-bold" for="QueryNickname">Query Nickname</label>
                <div class="col-lg-10">
                    <input class="form-control" type="text" name="QueryNickname" id="QueryNickname"
                           placeholder="Bot Nickname"
                           @isset($update) @if($update == 1) value="{{$server->qa_nickname}}" @endif @endisset
                           maxlength="11">
                    <div class="form-text">
                        Wähle einen Namen mit dem sich der Bot auf deinen Teamspeak verbinden soll.
                    </div>
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-lg-2 col-form-label fw-bold" for="Description">Beschreibung</label>
                <div class="col-lg-10">
                    @isset($update)
                        @if($update == 1)
                            <textarea class="form-control" name="Description" id="Description" rows="3"
                                      maxlength="255">{{$server->description}}</textarea>
                        @endif
                    @else
                        <textarea class="form-control" name="Description" id="Description" rows="3"
                                  maxlength="255" placeholder="Hier ist Platz für deine Notizen"></textarea>
                    @endisset
                </div>
            </div>
            <hr>
            <div class="row mb-2">
                @isset($update)
                    <div class="col-lg-3 d-grid">
                        <button class="btn btn-primary" type="submit" name="ServerID" id="ServerID" value="{{$server->id}}">Speichern</button>
                    </div>
                @else
                    <div class="col-lg-3 d-grid">
                        <button class="btn btn-primary" type="submit">Server anlegen</button>
                    </div>
                @endisset
            </div>
        </form>
    </div>
@endsection
