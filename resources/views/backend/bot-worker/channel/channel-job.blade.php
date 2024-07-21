@extends('template')

@section('site-title')
    Channel Creator | PS-Bot
@endsection

@section('custom-css')
    <link href="{{asset('css/font-awesome/css/fontawesome.css')}}" rel="stylesheet">
    <link href="{{asset('css/font-awesome/css/brands.css')}}" rel="stylesheet">
    <link href="{{asset('css/font-awesome/css/solid.css')}}" rel="stylesheet">
@endsection

@section('content')
    <div class="container mt-3">
        <div class="row mb-2">
            <div class="col-lg-12">
                <h1 class="fs-3 fw-bold">Channel Jobs</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                <div class="card">
                    <a href="{{Route('channel.view.createJobChannel')}}" class="text-decoration-none text-dark">
                        <div class="card-body">
                            <h5 class="card-title fs-5 fw-bold">
                                <i class="fa-solid fa-circle-plus"></i> Channel Job Hinzufügen
                            </h5>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <hr>
        @include('form-components.alertCustomError')
        <div class="row">
            <div class="col-lg-12">
                @if($jobs->count() == 0)
                    <div class="alert alert-primary" role="alert">
                        Es sind noch keine Channels hinzugefügt.
                    </div>
                @else
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col" class="col-lg-3">Channel</th>
                        <th scope="col" class="col-lg-1">Event</th>
                        <th scope="col" class="col-lg-3">Aktion</th>
                        <th scope="col" class="col-lg-2">User Aktion</th>
                        <th scope="col" class="col-lg-1">Benachrichtigung</th>
                        <th scope="col" class="col-lg-1">Erstellt am</th>
                        <th scope="col" class="col-lg-1"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($jobs as $job)
                        <tr>
                            <td>{{$job->rel_channels()->first()->channel_name}}</td>
                            <td>{{$job->on_event}}</td>
                            <td>{{$job->rel_actions()->first()->action_name}}</td>
                            <td>{{$job->rel_action_users()->first()->action_name}}</td>
                            <td>@if($job->notify_message_server_group == 1)
                                    Ja
                                @else
                                    Nein
                                @endif</td>
                            <td>{{date('d.m.Y', strtotime($job->created_at))}}</td>
                            <td>
                                <div class="d-flex justify-content-end">
                                    <form class="m-0 p-0" method="post" action="{{Route('channel.view.upsertJobChannel')}}">
                                        @csrf
                                        <button class="btn btn-link text-primary m-0 p-0 me-2" type="submit" name="JobID" value="{{ $job->id }}"><i class="fa-solid fa-pen-to-square"></i></button>
                                    </form>
                                    <form class="m-0 p-0">
                                        <button class="btn btn-link text-danger m-0 p-0 me-2" type="button" data-bs-toggle="modal" data-bs-target="#JobDelete{{ $job->id }}"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
@endsection

@foreach($jobs as $job)
    @include('backend.bot-worker.channel.inc.inc-channel-job-delete', ['job'=>$job])
@endforeach
