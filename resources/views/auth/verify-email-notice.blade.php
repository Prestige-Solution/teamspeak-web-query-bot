@extends('template')

@section('site-title')
    E-Mail bestätigen | PS-Bot
@endsection

@section('content')
    <div class="container my-auto">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                @include('form-components.successCustom')
                <div class="alert alert-secondary" role="alert">
                    <h4 class="alert-heading">E-Mail Bestätigung</h4>
                    <p>Wir freuen uns dich begrüßen zu dürfen. Um alle Funktionen nutzen zu können bestätige bitte deine E-Mail-Adresse.
                        Wir haben dir dafür eine Bestätigungsnachricht per E-Mail zugesandt.
                    </p>
                    <hr>
                    <p class="mb-0">Solltest du keine Nachricht erhalten haben klicke bitte auf den nachfolgenden Button. <br>
                    </p><br>
                    <p class="fw-bold">Bitte beachte dass du für die Bestätigung eingeloggt sein musst.</p>
                    <form method="post" action="{{Route('verification.send')}}">
                        @csrf
                        <button class="btn btn-success mt-2" name="sendMailVerification">Link erneut senden</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection