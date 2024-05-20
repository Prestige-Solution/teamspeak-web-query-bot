@extends('template')

@section('site-title')
    Channel Remover | PS-Bot
@endsection

@section('content')
    <div class="container mt-3">
        <div class="row mb-2">
            <div class="col-lg-12">
                <h1 class="fs-3 fw-bold">Channel Remover</h1>
            </div>
        </div>
        <form method="get" action="#">
            <div class="row">
                <div class="col-lg-auto">
                    <a href="{{Route('worker.view.createOrUpdateChannelRemover')}}"
                       class="btn btn-primary">Channel hinzufügen</a>
                </div>
            </div>
        </form>
        <hr>
        @include('form-components.alertCustomError')
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
                            <th scope="col">Sub-Channel von Channels</th>
                            <th scope="col">Sub-Channels löschen</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($channelLists as $channelList)
                        <tr role="button"
                            onclick="window.location='{{Route('worker.view.createOrUpdateChannelRemover',['update'=>1, 'server_id'=>$serverID,'remover_id'=>$channelList->id])}}'">
                            <td class="col-lg-3">{{$channelList->rel_ts3ChannelsRemover()->first()->channel_name}}</td>
                            <td class="col-lg-4">
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
                            <td class="col-lg-5">
                                @if($channelList->active == 1)
                                    <span class="badge bg-success">Aktiv</span>
                                @else
                                    <span class="badge bg-danger">Inaktiv</span>
                                @endif
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
