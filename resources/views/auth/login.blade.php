@extends('template')

@section('site-title')
    Login | PS-Bot
@endsection

@section('content')
    <div class="container my-auto">
        <div class="row justify-content-center">
            <div class="col-lg-2 d-flex justify-content-center">
                <img class="img-fluid" src="{{asset('storage/img/ps-bot.png')}}" alt="PS-Bot Logo" oncontextmenu="return false" style="width: 18rem;">
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0">
                    <div class="card-body">
                        <h1 class="fw-bold fs-5 mb-3">PS-Bot | Login</h1>
                        @include('form-components.alertHandlingLogin')
                        <form method="post" action="{{Route('logging-in')}}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="NickName">Benutzername:</label>
                                <input class="form-control" type="text" id="NickName" name="NickName" placeholder="Hansi">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="Password">Passwort:</label>
                                <input class="form-control" type="password" id="Password" name="Password" placeholder="Gib dein Passwort ein">
                            </div>
                            <div class="row mb-3">
                                <div class="col-6 d-grid mx-auto">
                                    <button class="btn btn-primary">Login</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection