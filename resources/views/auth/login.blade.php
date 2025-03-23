@extends('template')

@section('site-title')
    Login | {{ config('app.project') }}
@endsection

@section('content')
    <div class="container my-auto">
        <div class="row justify-content-center">
            <div class="col-lg-2 d-flex justify-content-center">
                <img class="img-fluid" src="{{asset('storage/img/bot-logo.png')}}" alt="PS-Bot Logo" oncontextmenu="return false" style="width: 18rem;">
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0">
                    <div class="card-body">
                        <h1 class="fw-bold fs-5 mb-3">Web Query Bot | Login</h1>
                        @include('form-components.alertHandlingLogin')
                        <form method="post" action="{{Route('logging-in')}}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="nickname">Nickname:</label>
                                <input class="form-control" type="text" id="nickname" name="nickname" placeholder="Nickname">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="password">Password:</label>
                                <input class="form-control" type="password" id="password" name="password" placeholder="Password">
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
