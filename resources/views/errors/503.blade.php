@extends('template')

@section('site-title')
    Maintenance | {{config('app.project')}}
@endsection

@section('content')
    <div class="container my-auto">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <img class="card-img w-25" src="{{asset('storage/img/bot-logo.png')}}" alt="Das Logo von PS-Bot">
                <h1 class="fw-bold mt-5">Maintenance</h1>
                <p class="text-muted mt-5 fs-5">
                    Please be patient until we are finished with the maintenance. The bot is not available and offline during this time.
                </p>
            </div>
        </div>
    </div>
@endsection
