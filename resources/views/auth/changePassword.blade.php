@extends('template')

@section('site-title')
    Passwort ändern | PS-Bot
@endsection

@section('content')
    <div class="container my-auto">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0">
                    <div class="card-body">
                        @include('form-components.alertHandlingLogin')
                        <form method="post" action="{{Route('backend.update.changePassword')}}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="CurrentPassword">Aktuelles Passwort:</label>
                                <input class="form-control" type="password" id="CurrentPassword" name="CurrentPassword" placeholder="Aktuelles Passwort">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="NewPassword">Neues Passwort:</label>
                                <input class="form-control" type="password" id="NewPassword" name="NewPassword" placeholder="Neues Passwort">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="NewPassword_confirmation">Neues Passwort wiederholen:</label>
                                <input class="form-control" type="password" id="NewPassword_confirmation" name="NewPassword_confirmation" placeholder="Neues Passwort wiederholen">
                            </div>
                            <div class="row mb-3">
                                <div class="col-6 d-grid">
                                    <button class="btn btn-primary">Password ändern</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection