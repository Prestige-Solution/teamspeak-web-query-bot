@extends('template')

@section('site-title')
    Channel Creator | PS-Bot
@endsection

@section('content')
    <div class="container mt-3">
        <div class="row mb-2">
            <div class="col-lg-12">
                <h1 class="fs-3 fw-bold">Channel Jobs</h1>
            </div>
        </div>
        <form method="get" action="#">
            <div class="row">
                <div class="col-lg-auto">
                    <a href="{{Route('channel.view.createOrUpdateJobChannel', ['server_id'=>$serverID])}}"
                       class="btn btn-primary">Channel hinzufügen</a>
                </div>
            </div>
        </form>
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
                        <th scope="col">Channel</th>
                        <th scope="col">Event</th>
                        <th scope="col">Aktion</th>
                        <th scope="col">User Aktion</th>
                        <th scope="col">Benachrichtigung</th>
                        <th scope="col">Erstellt am</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($jobs as $job)
                        <tr role="button"
                            onclick="window.location='{{Route('channel.view.createOrUpdateJobChannel',['update'=>1, 'server_id'=>$serverID,'job_id'=>$job->id])}}'">
                            <td class="col-lg-3">{{$job->rel_channels()->first()->channel_name}}</td>
                            <td class="col-lg-1">{{$job->on_event}}</td>
                            <td class="col-lg-3">{{$job->rel_actions()->first()->action_name}}</td>
                            <td class="col-lg-3">{{$job->rel_action_users()->first()->action_name}}</td>
                            <td class="col-lg-1">@if($job->notify_message_server_group == 1)
                                    Ja
                                @else
                                    Nein
                                @endif</td>
                            <td class="col-lg-1">{{date('d.m.Y', strtotime($job->created_at))}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
@endsection