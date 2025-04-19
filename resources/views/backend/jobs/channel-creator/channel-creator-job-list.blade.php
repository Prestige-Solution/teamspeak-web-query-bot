@extends('template')

@section('site-title')
    Channel Creator | {{ config('app.project') }}
@endsection

@section('content')
<div class="container mt-3 mb-2">
    <div class="row mb-2">
        <div class="col-lg-12">
            <h1 class="fs-3 fw-bold">Channel Creator | {{ \Illuminate\Support\Facades\Auth::user()->rel_server->server_name }} </h1>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-lg-3">
            <div class="card">
                <div class="card-body">
                    <button type="button"
                            class="btn btn-link m-0 p-0 text-start text-decoration-none text-dark fw-bold fs-5"
                            data-bs-toggle="modal"
                            data-bs-target="#CreateChannelJob">
                        <i class="fa-solid fa-circle-plus"></i> Add Channel Creator
                    </button>
                </div>
            </div>
        </div>
    </div>
    <hr>
    @include('form-components.alertCustomError')
    @include('form-components.successCustom')
    <div class="row">
        <div class="col-lg-12">
            @if($jobs->count() == 0)
                <div class="alert alert-primary" role="alert">
                    No jobs have been added yet.
                </div>
            @else
            <div class="accordion" id="accordionChannels">
                @foreach($jobs as $job)
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#panelID-{{$job->id}}" aria-expanded="true" aria-controls="panelID-{{$job->id}}">
                                @if($job->is_active == true)
                                    <i class="fa-solid fa-circle me-2 text-success"></i>
                                @else
                                    <i class="fa-solid fa-circle me-2 text-danger"></i>
                                @endif
                                    {{ $job->rel_channels()->first()->channel_name }}
                            </button>
                        </h2>
                        <div id="panelID-{{$job->id}}" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <div class="row d-flex justify-content-end">
                                    <div class="col-lg-12 d-flex justify-content-between">
                                        <div class="m-0 p-0">
                                            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#JobUpdate{{$job->id}}"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                                        </div>
                                        <div>
                                            <button class="btn btn-danger" type="button" data-bs-toggle="modal" data-bs-target="#JobDelete{{$job->id}}"><i class="fa-solid fa-trash"></i> Delete</button>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-bold">Event:</span>
                                                <span class="text-secondary">{{$job->rel_bot_event->event_name}}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-bold">Action:</span>
                                                <span class="text-secondary">{{$job->rel_actions()->first()->action_name}}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-bold">Number of Clients:</span>
                                                <span class="text-secondary">{{$job->action_min_clients}}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-bold">Max. Channels:</span>
                                                <span class="text-secondary">{{$job->create_max_channels}}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-bold">Current count cub-channels</span>
                                                <span class="text-secondary">{{$job->rel_pid->count()}}</span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-lg-6">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-bold">Client action:</span>
                                                <span class="text-secondary">{{$job->rel_action_users()->first()->action_name}}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-bold">Client channel group:</span>
                                                <span class="text-secondary">
                                                    @if($job->channel_cgid !== 0)
                                                        {{$job->rel_cgid->name}}
                                                    @else
                                                        No group
                                                    @endif
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-bold">Channel Template:</span>
                                                <span class="text-secondary">
                                                    @if($job->channel_template_cid !== 0)
                                                        {{$job->rel_template_channel->channel_name}}
                                                    @else
                                                        No template
                                                    @endif
                                                </span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-bold">Notify group:</span>
                                                <span class="text-secondary">
                                                    @if($job->is_notify_message_server_group == true)
                                                        {{$job->rel_sgid->name}}
                                                    @else
                                                        No notification
                                                    @endif
                                                </span>
                                            </li>
                                        </ul>
                                        @if(!empty($job->notify_message_server_group_message))
                                            <hr class="bg-secondary">
                                            <p class="fw-bold">Notify Message:</p>
                                            <p>{{$job->notify_message_server_group_message}}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@include('backend.jobs.channel-creator.inc.create-channel-job', ['tsChannels'=>$tsChannels,'tsChannelTemplates'=>$tsChannelTemplates,'botEvents'=>$botEvents,'botActions'=>$botActions,'botActionUsers'=>$botActionUsers,'tsServerGroups'=>$tsServerGroups,'tsChannelGroups'=>$tsChannelGroups])

@foreach($jobs as $job)
    @include('backend.jobs.channel-creator.inc.update-channel-job', ['job'=>$job,'tsChannels'=>$tsChannels,'tsChannelTemplates'=>$tsChannelTemplates,'botEvents'=>$botEvents,'botActions'=>$botActions,'botActionUsers'=>$botActionUsers,'tsServerGroups'=>$tsServerGroups,'tsChannelGroups'=>$tsChannelGroups])
@endforeach

@foreach($jobs as $job)
    @include('backend.jobs.channel-creator.inc.delete-channel-job', ['job'=>$job])
@endforeach

@include('backend.jobs.channel-creator.inc.inc-message-pattern')
