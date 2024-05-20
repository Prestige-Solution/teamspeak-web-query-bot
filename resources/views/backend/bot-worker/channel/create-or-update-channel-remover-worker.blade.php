@extends('template')

@section('site-title')
    Channel Remover erstellen | PS-Bot
@endsection

@section('content')
<div class="container mt-3">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="fs-3 fw-bold">Channel Remover erstellen</h1>
        </div>
    </div>
    <hr>
    @include('form-components.alertCustomError')
    <form method="post" action="{{Route('worker.create.newChannelRemover')}}">
        @csrf
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label fw-bold" for="ChannelCid">Channel</label>
            <div class="col-lg-8">
                <select class="form-select" id="ChannelCid" name="ChannelCid">
                    <option selected disabled>Bitte wählen</option>
                    @foreach($ts3Channels->where('pid','=',0) as $ts3Channel)
                        <option value="{{$ts3Channel->cid}}" @isset($update) @if($ts3Channel->cid == $channelRemoverSetting->channel_cid) selected @endif @endisset>{{$ts3Channel->channel_name}}</option>
                        @foreach($ts3Channels->where('pid','=',$ts3Channel->cid) as $tsChannelPID)
                            <option value="{{$tsChannelPID->cid}}" @isset($update) @if($tsChannelPID->cid == $channelRemoverSetting->channel_cid) selected @endif @endisset>{{$tsChannelPID->channel_name}}</option>
                        @endforeach
                    @endforeach
                </select>
                <div class="form-text">
                    Wirkt auf alle Sub-Channels des gewählten Channels
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label fw-bold" for="MaxIdleTime">Unbenutzt seit</label>
            <div class="col-lg-4">
                <input class="form-control" type="number" name="MaxIdleTime" id="MaxIdleTime" min="1"
                @isset($update)
                    @if($channelRemoverSetting->channel_max_time_format == 'm')
                        value="{{$channelRemoverSetting->channel_max_seconds_empty / 60}}"
                    @endif
                    @if($channelRemoverSetting->channel_max_time_format == 'h')
                        value="{{$channelRemoverSetting->channel_max_seconds_empty / (60 * 60)}}"
                    @endif
                    @if($channelRemoverSetting->channel_max_time_format == 'd')
                        value="{{$channelRemoverSetting->channel_max_seconds_empty / (24*60*60)}}"
                    @endif
                @else
                    value="1"
                @endisset>
            </div>
            <div class="col-lg-4">
                <select class="form-select" name="MaxIdleTimeFormat" id="MaxIdleTimeFormat" aria-label="MaxIdleTimeFormat">
                    <option value="m" @isset($update) @if($channelRemoverSetting->channel_max_time_format == 'm') selected @endif @endisset>Minuten</option>
                    <option value="h" @isset($update) @if($channelRemoverSetting->channel_max_time_format == 'h') selected @endif @endisset>Stunden</option>
                    <option value="d" @isset($update) @if($channelRemoverSetting->channel_max_time_format == 'd') selected @endif @endisset>Tage</option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label fw-bold" for="ChannelRemoverActive">Status</label>
            <div class="col-lg-4">
                <select class="form-select" name="ChannelRemoverActive" id="ChannelRemoverActive">
                    <option value="0" @isset($update) @if($channelRemoverSetting->active == 0) selected @endif @endisset>Inaktiv</option>
                    <option value="1" @isset($update) @if($channelRemoverSetting->active == 1) selected @endif @endisset>Aktiv</option>
                </select>
            </div>
        </div>
        <hr>
        <div class="row mb-3">
            <div class="col-lg-2 d-grid">
                <button class="btn btn-primary" name="ServerID" value="{{$serverID}}">Speichern</button>
            </div>
            @isset($update)
            <div class="col-lg-2 d-grid">
                <button class="btn btn-danger" name="DeleteID" value="{{$channelRemoverSetting->id}}">Löschen</button>
            </div>
            @endisset
        </div>
    </form>
</div>
@endsection
