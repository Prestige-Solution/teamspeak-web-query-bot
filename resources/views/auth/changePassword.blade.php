@extends('template')

@section('site-title')
    Change password | {{ config('app.project') }}
@endsection

@section('content')
    <div class="container mt-3 mb-3">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="fw-bold">Change Passwort</h2>
            </div>
        </div>
        <hr>
        <form method="post" action="{{route('backend.update.changePassword')}}">
            @csrf
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <button type="submit"
                                class="btn btn-link m-0 p-0 text-start text-decoration-none text-dark fw-bold fs-5">
                            <i class="fa-solid fa-floppy-disk"></i> Submit
                        </button>
                    </div>
                </div>
            </div>
            <hr>
            @include('form-components.alertCustomError')
            @include('form-components.successCustom')
            <div class="row">
                <div class="col-lg-12">
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="CurrentPassword">Current Password:</label>
                        <input class="form-control" type="password" id="CurrentPassword" name="CurrentPassword" placeholder="Current password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="NewPassword">New password:</label>
                        <input class="form-control" type="password" id="NewPassword" name="NewPassword" placeholder="New password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="NewPassword_confirmation">Confirm new password:</label>
                        <input class="form-control" type="password" id="NewPassword_confirmation" name="NewPassword_confirmation" placeholder="Confirm new password">
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
