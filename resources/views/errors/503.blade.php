@extends('template')

@section('site-title')
    Wartungsarbeiten | PS-Bot
@endsection

@section('content')
    <div class="container my-auto">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <img class="card-img w-25" src="{{asset('storage/img/bot-logo.png')}}" alt="Das Logo von PS-Bot">
                <h1 class="fw-bold mt-5">Wartungsarbeiten</h1>
                <p class="text-muted mt-5 fs-5">
                    Bitte habe etwas Geduld bis wir mit der Wartung fertig sind. Der Bot ist in dieser Zeit nicht Verfügbar und offline.
                </p>
            </div>
        </div>
    </div>
@endsection
