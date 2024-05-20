@extends('template')

@section('site-title')
    Query Bot verifizieren | PS-Bot
@endsection

@section('content')
    <div class="container my-auto">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                @include('form-components.alertCustomError')
                @include('form-components.customErrorSuccess')
                <div class="alert alert-secondary" role="alert">
                    <h4 class="alert-heading">Query Bot bestätigen</h4>
                    <p>
                        Hurra, du hast deinen Server eingetragen. Wenn alles funktioniert hat und der Bot seine Rechte alle korrekt erhalten hat
                        müsste die <b>Server Admin Gruppe einen Token via Textchat</b> erhalten haben. Bitte trage diesen Aktivierungstoken hier ein.
                        Im Anschluss wird dein Server initialisiert. Bei Problemen wirf einen Blick auf die Bot-Logs
                    </p>
                    <hr>
                    <p class="mb-0 fw-bold">
                        Achtung: <br>
                        - Bei jeder IP Änderung muss der Bot erneut bestätigt werden.<br>
                        - Der Token wird an alle Clients in der Server Gruppe <b>"Server Admin"</b> gesendet<br>
                        - Bei Problemen folge bitte dem Tutorial oder wende dich an unseren Support.
                    </p>
                    <hr>
                    <form method="post" action="{{Route('backend.update.verifyBot')}}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="BotToken">Bot Aktivierungstoken</label>
                            <input class="form-control" type="password" id="BotToken" name="BotToken">
                        </div>
                        <button type="submit" class="btn btn-primary" name="BotVerifyID" value="{{\Illuminate\Support\Facades\Auth::user()->server_id}}">Bot verifizieren</button>
                        <button type="submit" class="btn btn-primary" name="BotSendVerifyID" value="{{\Illuminate\Support\Facades\Auth::user()->server_id}}">Token erneut senden</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection