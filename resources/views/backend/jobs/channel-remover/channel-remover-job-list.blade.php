@extends('template')

@section('site-title')
    Channel Remover | {{ config('app.project') }}
@endsection

@section('content')
<div class="container mt-3">
    <div class="row mb-2">
        <div class="col-lg-12">
            <h1 class="fs-3 fw-bold">Channel Remover | {{ \Illuminate\Support\Facades\Auth::user()->rel_server->server_name }}</h1>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <button type="button"
                            class="btn btn-link m-0 p-0 text-start text-decoration-none text-dark fw-bold fs-5"
                            data-bs-toggle="modal"
                            data-bs-target="#CreateRemoverJob">
                        <i class="fa-solid fa-circle-plus"></i> Add Channel Remover
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
                There are no channels added yet.
            </div>
        @else
            <div class="accordion" id="accordionChannels">
                @foreach($jobs as $job)
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#panelsID-{{$job->id}}" aria-expanded="false" aria-controls="panelsID-{{$job->id}}">
                                @if($job->is_active == true)
                                    <i class="fa-solid fa-circle me-2 text-success"></i>
                                @else
                                    <i class="fa-solid fa-circle me-2 text-danger"></i>
                                @endif
                                    {{$job->rel_channels()->first()->channel_name}}
                            </button>
                        </h2>
                        <div id="panelsID-{{$job->id}}" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <div class="row d-flex justify-content-end">
                                    <div class="col-lg-12 d-flex justify-content-between">
                                        <div class="m-0 p-0">
                                            <button class="btn btn-primary" type="submit" data-bs-toggle="modal" data-bs-target="#UpdateRemoverJob{{$job->id}}"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                                        </div>
                                        <div>
                                            <button class="btn btn-danger" type="button" data-bs-toggle="modal" data-bs-target="#ChannelRemoverDelete{{$job->id}}"><i class="fa-solid fa-trash"></i> Delete</button>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-bold">Max. unused time:</span>
                                                <span class="text-secondary">
                                                    @if($job->channel_max_time_format == 'm')
                                                        {{$job->channel_max_seconds_empty / 60}} minute/s
                                                    @endif
                                                    @if($job->channel_max_time_format == 'h')
                                                        {{$job->channel_max_seconds_empty / (60 * 60)}} hour/s
                                                    @endif
                                                    @if($job->channel_max_time_format == 'd')
                                                        {{$job->channel_max_seconds_empty / (24*60*60)}} day/s
                                                    @endif
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-lg-6">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span class="fw-bold">Last run:</span>
                                                <span class="text-secondary">{{\Illuminate\Support\Carbon::parse($job->updated_at)->format('d.m.Y - H:i:s')}}</span>
                                            </li>
                                        </ul>
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

@include('backend.jobs.channel-remover.inc.create-channel-job', ['tsChannels' => $tsChannels])

@foreach($jobs as $job)
    @include('backend.jobs.channel-remover.inc.update-channel-job', ['job'=>$job])
@endforeach

@foreach($jobs as $job)
    @include('backend.jobs.channel-remover.inc.delete-channel-job', ['job'=>$job])
@endforeach
