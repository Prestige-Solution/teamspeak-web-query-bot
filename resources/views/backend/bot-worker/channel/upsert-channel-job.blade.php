@extends('template')

@section('site-title')
    Create Channel Job | PS-Bot
@endsection

@section('content')
<div class="container mt-3">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="fs-3 fw-bold">Channel Job erstellen</h1>
        </div>
    </div>
    <hr>
    @include('form-components.alertCustomError')
    <form method="post" action="{{Route('channel.upsert.channelJob')}}">
        @csrf
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label fw-bold" for="ChannelTarget">Channel wählen</label>
            <div class="col-lg-8">
                <select class="form-select" name="ChannelTarget" id="ChannelTarget">
                    <option selected disabled>Bitte wählen</option>
                    @foreach($tsChannels->where('pid','=',0) as $tsChannel)
                        <option value="{{$tsChannel->cid}}" @isset($update) @if($update == 1 && $ts3BotJob->on_cid == $tsChannel->cid) selected @endif @endisset>{{$tsChannel->channel_name}}</option>
                        @foreach($tsChannels->where('pid','=',$tsChannel->cid) as $tsChannelPID)
                            <option value="{{$tsChannelPID->cid}}" @isset($update) @if($update == 1 && $ts3BotJob->on_cid == $tsChannelPID->cid) selected @endif @endisset>{{$tsChannelPID->channel_name}}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label fw-bold" for="ChannelEvent">Ereignis</label>
            <div class="col-lg-8">
                <select class="form-select" name="ChannelEvent" id="ChannelEvent">
                    <option selected disabled>Bitte wählen</option>
                    @foreach($botEvents as $botEvent)
                        <option value="{{$botEvent->event_ts}}" @isset($update) @if($update == 1 && $ts3BotJob->on_event == $botEvent->event_ts) selected @endif @endisset>{{$botEvent->event_name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label fw-bold" for="ChannelAction">Aktion</label>
            <div class="col-lg-8">
                <select class="form-select" name="ChannelAction" id="ChannelAction">
                    <option selected disabled>Bitte wählen</option>
                    @foreach($botActions as $botAction)
                        <option value="{{$botAction->id}}" @isset($update) @if($update == 1 && $ts3BotJob->action_id == $botAction->id) selected @endif @endisset>{{$botAction->action_name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label fw-bold" for="ChannelActionMinClientCount">Anzahl Clients</label>
            <div class="col-lg-8">
                <input class="form-control" type="number" name="ChannelActionMinClientCount" id="ChannelActionMinClientCount" min="1" @isset($update) @if($update == 1) value="{{$ts3BotJob->action_min_clients}}"  @endif @else value="1" @endisset>
                <div id="ChannelActionMinClientCountHelp" class="form-text">Gibt an wie viele Clients in dem Channel vorhanden sein müssen damit die Aktion ausgeführt wird. (Standard ist 1 = sofort)</div>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label fw-bold" for="MaxChannels">Max. Channels</label>
            <div class="col-lg-8">
                <input class="form-control" type="number" name="MaxChannels" id="MaxChannels" min="0" @isset($update) @if($update == 1) value="{{$ts3BotJob->create_max_channels}}" @endif @else value="10" @endisset>
                <div class="form-text" id="MaxChannelsHelp">Anzahl der Channels, die maximal durch den Bot erstellt werden können (0 = unbegrenzt)</div>
            </div>
        </div>
        <hr>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label fw-bold" for="ChannelActionUserInChannel">Client Action</label>
            <div class="col-lg-8">
                <select class="form-select" name="ChannelActionUserInChannel" id="ChannelActionUserInChannel">
                    @foreach($botActionUsers as $botActionUser)
                        <option value="{{$botActionUser->id}}" @isset($update) @if($update == 1 && $ts3BotJob->action_user_id == $botActionUser->id) selected @endif @endisset>{{$botActionUser->action_name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label fw-bold" for="ChannelActionUserInChannelGroup">Client Channel Group</label>
            <div class="col-lg-8">
                <select class="form-select" name="ChannelActionUserInChannelGroup" id="ChannelActionUserInChannelGroup">
                    <option selected value="0">Keine Aktion</option>
                    @foreach($tsChannelGroups as $channelGroup)
                        <option value="{{$channelGroup->cgid}}" @isset($update) @if($update == 1 && $ts3BotJob->channel_cgid == $channelGroup->cgid) selected @endif @endisset>{{$channelGroup->name}}</option>
                    @endforeach
                </select>
                <div id="NotifyServerGroupSgidHelp" class="form-text">Gibt an ob eine Channel-Gruppe an den Client vergeben werden soll (Wenn Channel erstellt und/oder Client verschoben wird)</div>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label fw-bold" for="ChannelTemplate">Channel Template</label>
            <div class="col-lg-8">
                <select class="form-select" name="ChannelTemplate" id="ChannelTemplate">
                    <option value="0" selected>Kein Template</option>
                    @foreach($tsChannelTemplates as $tsChannelTemplate)
                        <option value="{{$tsChannelTemplate->id}}" @isset($update) @if($tsChannelTemplate->id == $ts3BotJob->channel_template_id) selected @endif @endisset>{{$tsChannelTemplate->channel_name}}</option>
                    @endforeach
                </select>
                <div class="form-text">Kopiert die Rechte des ausgewählten Channels</div>
            </div>
        </div>
        <hr>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label fw-bold" for="NotifyServerGroupBool">Info an Servergruppe</label>
            <div class="col-lg-8">
                <select class="form-select" name="NotifyServerGroupBool" id="NotifyServerGroupBool">
                    <option selected value="0" @isset($update) @if($update == 1 && $ts3BotJob->notify_message_server_group == 0) selected @endif @endisset>Nein</option>
                    <option value="1" @isset($update) @if($update == 1 && $ts3BotJob->notify_message_server_group == 1) selected @endif @endisset>Ja</option>
                </select>
                <div id="NotifyServerGroupSgidHelp" class="form-text">Gibt an ob eine Servergruppe informiert werden soll</div>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label fw-bold" for="NotifyServerGroupSgid">Servergruppe</label>
            <div class="col-lg-8">
                <select class="form-select" name="NotifyServerGroupSgid" id="NotifyServerGroupSgid">
                    <option selected value="0">Keine</option>
                    @foreach($tsServerGroups as $tsServerGroup)
                        <option value="{{$tsServerGroup->sgid}}" @isset($update) @if($update == 1 && $ts3BotJob->notify_message_server_group_sgid == $tsServerGroup->sgid) selected @endif @endisset>{{$tsServerGroup->name}}</option>
                    @endforeach
                </select>
                <div id="NotifyServerGroupSgidHelp" class="form-text">Gib die Servergruppe an welche informiert werden soll.</div>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label fw-bold" for="NotifyServerGroupMessage">Nachricht</label>
            <div class="col-lg-8">
                <input class="form-control" type="text" name="NotifyServerGroupMessage" id="NotifyServerGroupMessage" @isset($update) @if($update == 1) value="{{$ts3BotJob->notify_message_server_group_message}}" @endif @endisset>
                <div id="NotifyServerGroupMessageHelp" class="form-text">Nutze Platzhalter um deine Nachrichten noch individueller zu gestalten. <a href="#patternList" data-bs-toggle="modal" data-bs-target="#patternList">Liste anzeigen</a>.</div>
            </div>
        </div>
        @include('inc.bot-config.message-pattern')
        <hr>
        <div class="row mb-3">
            <div class="col-lg-2 d-grid">
                <button class="btn btn-primary" name="ServerID" value="{{$serverID}}">Speichern</button>
            </div>
        </div>
    </form>
</div>
@endsection
