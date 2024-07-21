@extends('template')

@section('site-title')
    Channel Remover | PS-Bot
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
                <h1 class="fs-3 fw-bold">Channel Remover</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <a href="{{Route('worker.view.createChannelRemover')}}" class="text-decoration-none text-dark">
                        <div class="card-body">
                            <h5 class="card-title fs-5 fw-bold">
                                <i class="fa-solid fa-circle-plus"></i> Channel Remover Job Hinzufügen
                            </h5>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <hr>
        @include('form-components.alertCustomError')
        @include('form-components.successCustom')
        <div class="row">
            <div class="col-lg-12">
                @if($channelLists->count() == 0)
                    <div class="alert alert-primary" role="alert">
                        Es sind noch keine Channels hinzugefügt.
                    </div>
                @else
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col" class="col-lg-5">Sub-Channel von Channels</th>
                            <th scope="col" class="col-lg-3">Sub-Channels löschen</th>
                            <th scope="col" class="col-lg-1">Status</th>
                            <th scope="col" class="col-lg-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($channelLists as $channelList)
                        <tr>
                            <td>{{$channelList->rel_ts3ChannelsRemover()->first()->channel_name}}</td>
                            <td>
                                @if($channelList->channel_max_time_format == 'm')
                                   ungenutzt seit {{$channelList->channel_max_seconds_empty / 60}} Minute/n
                                @endif
                                @if($channelList->channel_max_time_format == 'h')
                                    ungenutzt seit {{$channelList->channel_max_seconds_empty / (60 * 60)}} Stunde/n
                                @endif
                                @if($channelList->channel_max_time_format == 'd')
                                    ungenutzt seit {{$channelList->channel_max_seconds_empty / (24*60*60)}} Tag/en
                                @endif
                            </td>
                            <td>
                                @if($channelList->active == 1)
                                    <span class="badge bg-success">Aktiv</span>
                                @else
                                    <span class="badge bg-danger">Inaktiv</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-end">
                                    <form class="m-0 p-0" method="post" action="{{Route('worker.view.upsertChannelRemover')}}">
                                        @csrf
                                        <button class="btn btn-link text-primary m-0 p-0 me-2" type="submit" name="RemoveID" value="{{ $channelList->id }}"><i class="fa-solid fa-pen-to-square"></i></button>
                                    </form>
                                    <form class="m-0 p-0">
                                        <button class="btn btn-link text-danger m-0 p-0 me-2" type="button" data-bs-toggle="modal" data-bs-target="#ChannelRemoverDelete{{ $channelList->id }}"><i class="fa-solid fa-trash"></i></button>
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

@foreach($channelLists as $channelList)
    @include('backend.bot-worker.channel.inc.inc-channel-remover-delete', ['remove'=>$channelList])
@endforeach
