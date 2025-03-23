@extends('template')

@section('site-title')
    Change password | {{ config('app.project') }}
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
                                <label class="form-label fw-bold" for="CurrentPassword">Current Password:</label>
                                <input class="form-control" type="password" id="CurrentPassword" name="CurrentPassword" placeholder="Current password">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="NewPassword">New password:</label>
                                <input class="form-control" type="password" id="NewPassword" name="NewPassword" placeholder="New password">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="NewPassword_confirmation">Confirm password:</label>
                                <input class="form-control" type="password" id="NewPassword_confirmation" name="NewPassword_confirmation" placeholder="Confirm password">
                            </div>
                            <div class="row mb-3">
                                <div class="col-6 d-grid">
                                    <button class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
