@extends('template')

@section('site-title')
    Registrieren | PS-Bot
@endsection

@section('content')
    <div class="container mt-3">
        <div class="row justify-content-center">
            <div class="col-lg-1 d-flex justify-content-center">
                <img class="img-fluid" src="{{asset('storage/img/ps-bot.png')}}" alt="PS-Bot Logo" oncontextmenu="return false" style="width: 18rem;">
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0">
                    <div class="card-body">
                        <h1 class="fw-bold fs-5 mb-3">PS-Bot | Registrierung</h1>
                        @if(config('app.app_register') == true)
                        @include('form-components.alertHandlingLogin')
                        <form method="post" action="{{Route('public.create.registerNewUser')}}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="NickName">Benutzername:</label>
                                <input class="form-control" type="text" id="NickName" name="NickName" placeholder="Hansi">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="Email">E-Mail:</label>
                                <input class="form-control" type="email" id="Email" name="Email" placeholder="Hansi@example.de">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="Birthday">Geburtsdatum:</label>
                                <input class="form-control" type="date" id="Birthday" name="Birthday">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="Password">Passwort:</label>
                                <input class="form-control" type="password" id="Password" name="Password" placeholder="Gib ein Passwort ein">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="Password_confirmation">Passwort Wiederholen:</label>
                                <input class="form-control" type="password" id="Password_confirmation" name="Password_confirmation" placeholder="Gib dein Passwort erneut ein">
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="" id="CheckTermsOfUse" required>
                                <label class="form-check-label" for="CheckTermsOfUse">
                                    Ich bestätige die <a href="{{Route('public.view.terms-of-use')}}">Nutzungsbedingungen</a>
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="" id="CheckDataPrivacy" required>
                                <label class="form-check-label" for="CheckDataPrivacy">
                                    Ich bestätige die <a href="{{Route('public.view.data-privacy')}}" target="_blank">Datenschutzerklärung</a>
                                </label>
                            </div>
                            <div class="row mb-3">
                                <div class="col-6 d-grid mx-auto">
                                    <button class="btn btn-primary">Registrieren</button>
                                </div>
                            </div>
                        </form>
                        @else
                            <div class="alert alert-secondary" role="alert">
                                Die Registrierung ist aktuell nicht möglich.<br><br>
                                Du interessierst dich für das Projekt und möchtest Zugang erhalten?<br>
                                Schreib uns gerne eine Nachricht an <a href="mailto:psbot@gamerboerse.de">psbot@gamerboerse.de</a> und wir informieren dich, sobald die Registrierung wieder möglich ist.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection