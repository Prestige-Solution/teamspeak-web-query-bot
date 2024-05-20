@extends('template')

@section('site-title')
    Einladungscode | PS-Bot
@endsection

@section('custom-css')
    <link href="{{asset('css/font-awesome/css/fontawesome.css')}}" rel="stylesheet">
    <link href="{{asset('css/font-awesome/css/brands.css')}}" rel="stylesheet">
    <link href="{{asset('css/font-awesome/css/solid.css')}}" rel="stylesheet">
@endsection

@section('content')
    <div class="container mt-3">
        <div class="row">
            <div class="col-lg-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#CreateNewInviteModal">Einladung hinzufügen</button>
            </div>
        </div>
        <hr>
        @include('form-components.alertCustomError')
        <div class="row">
            <div class="col-lg-12">
                @if($invites->count() != 0)
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">E-Mail-Adresse</th>
                        <th scope="col">Einladungscode</th>
                        <th scope="col">Gültig bis</th>
                        <th scope="col">Status</th>
                        <th scope="col"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($invites as $invite)
                        <tr>
                            <td class="col-lg-3">{{$invite->email}}</td>
                            <td class="col-lg-3">{{$invite->invite_code}}</td>
                            <td class="col-lg-3">{{date('d.m.Y - H:i:s',strtotime($invite->expire_at))}} Uhr</td>
                            <td class="col-lg-2">
                                @if($invite->invite_accepted == false)
                                    Ausstehend
                                @else
                                    Aktiv
                                @endif
                            </td>
                            <td class="col-lg-1">
                                <form method="post" action="{{Route('backend.delete.invite')}}">
                                    @csrf
                                    <button class="btn btn-link text-danger text-decoration-none p-0" name="InviteID" value="{{$invite->id}}"><i class="fa-solid fa-trash-can"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @else
                    <div class="alert alert-primary" role="alert">
                        Es wurden noch keine Einladungen hinzugefügt
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('inc.invite.create-new-invite')
@endsection