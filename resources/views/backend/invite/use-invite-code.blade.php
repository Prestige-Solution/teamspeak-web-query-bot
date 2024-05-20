@extends('template')

@section('site-title')
    Einladungscode | PS-Bot
@endsection

@section('content')
    <div class="container my-auto">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                @include('form-components.alertCustomError')
                <div class="alert alert-secondary" role="alert">
                    <h4 class="alert-heading">Einladungscode verwenden</h4>
                    <p>
                        Wenn dich der Servereigentümer zum Administrieren seines Servers eingeladen hat, kannst du hier
                        den Einladungscode eingeben.
                    </p>
                    <hr>
                    <p class="mb-0">Solltest du keinen Einladungscode erhalten haben bitte den Servereigentümer dir den Code mitzuteilen.<br>
                    </p>
                    <hr>
                    <form method="post" action="{{Route('backend.update.useInviteCode')}}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="InviteMail">E-Mail Adresse</label>
                            <input class="form-control" type="email" id="InviteMail" name="InviteMail" value="{{\Illuminate\Support\Facades\Auth::user()->email}}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="InviteCode">Einladungscode</label>
                            <input class="form-control" type="password" id="InviteCode" name="InviteCode" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Einladung annehmen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection