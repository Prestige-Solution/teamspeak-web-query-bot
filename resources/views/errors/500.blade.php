@extends('template')

@section('site-title')
    Oops, something went wrong | {{config('app.project')}}
@endsection

@section('content')
    <div class="container my-auto">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <img class="card-img w-25" src="{{asset('storage/img/bot-logo.png')}}" alt="Das Logo von PS-Bot">
                <h1 class="fw-bold mt-5">Oops, something went wrong</h1>
                <p class="text-muted mt-5 fs-5">
                    An error has occurred. We apologize for the inconvenience. If you like, you can report the error to us at <a href="https://github.com/Prestige-Solution/teamspeak-web-query-bot/issues" target="_blank">Github</a>
                </p>
            </div>
        </div>
    </div>
@endsection
