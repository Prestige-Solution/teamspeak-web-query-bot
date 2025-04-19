<div class="modal fade" id="JobUpdate{{$job->id}}" tabindex="-1" aria-labelledby="JobUpdate{{$job->id}}Label" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form class="was-validated" method="post" action="{{Route('channel.upsert.channelJob')}}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5 fw-bold" id="JobUpdate{{$job->id}}Label">Update channel job {{$job->rel_channels()->first()->channel_name}}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="accordion" id="accordionPanelsStayOpenChannel">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-channel" aria-expanded="true" aria-controls="panelsStayOpen-channel">
                                    Channel action settings
                                </button>
                            </h2>
                            <div id="panelsStayOpen-channel" class="accordion-collapse collapse show">
                                <div class="accordion-body">
                                    <div class="row mb-3">
                                        <label class="col-lg-2 col-form-label fw-bold" for="is_active">Status</label>
                                        <div class="col-lg-8">
                                            <select class="form-select" name="is_active" id="is_active" required>
                                                <option value="0" @if($job->is_active == false) selected @endif>Inactive</option>
                                                <option value="1" @if($job->is_active == true) selected @endif>Active</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-lg-2 col-form-label fw-bold" for="on_cid">Select channel</label>
                                        <div class="col-lg-8">
                                            <select class="form-select" name="on_cid" id="on_cid" required>
                                                <option selected disabled value="">Please choose</option>
                                                @foreach($tsChannels->where('pid','=',0) as $tsChannel)
                                                    <option value="{{$tsChannel->cid}}" @if($tsChannel->cid === $job->on_cid) selected @else disabled @endif>{{$tsChannel->channel_name}}</option>
                                                    @foreach($tsChannels->where('pid','=',$tsChannel->cid) as $tsChannelPID)
                                                        <option value="{{$tsChannelPID->cid}}" @if($tsChannelPID->cid === $job->on_cid) selected @else disabled @endif>-{{$tsChannelPID->channel_name}}</option>
                                                    @endforeach
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-lg-2 col-form-label fw-bold" for="on_event">Event</label>
                                        <div class="col-lg-8">
                                            <select class="form-select" name="on_event" id="on_event" required>
                                                <option selected disabled value="">Please choose</option>
                                                @foreach($botEvents as $botEvent)
                                                    <option value="{{$botEvent->event_ts}}" @if($botEvent->event_ts === $job->on_event) selected @endif>{{$botEvent->event_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-lg-2 col-form-label fw-bold" for="action_id">Action</label>
                                        <div class="col-lg-8">
                                            <select class="form-select" name="action_id" id="action_id" required>
                                                <option selected disabled value="">Please choose</option>
                                                @foreach($botActions as $botAction)
                                                    <option value="{{$botAction->id}}" @if($botAction->id === $job->action_id) selected @endif>{{$botAction->action_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-lg-2 col-form-label fw-bold" for="action_min_clients">Number of Clients</label>
                                        <div class="col-lg-8">
                                            <input class="form-control" type="number" name="action_min_clients" id="action_min_clients" min="1" value="{{$job->action_min_clients}}">
                                            <div id="action_min_clients_help" class="form-text">Number of clients to execute the action (Default = 1 immediately)</div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-lg-2 col-form-label fw-bold" for="create_max_channels">Max. channels</label>
                                        <div class="col-lg-8">
                                            <input class="form-control" type="number" name="create_max_channels" id="create_max_channels" min="0" value="{{$job->create_max_channels}}">
                                            <div class="form-text" id="create_max_channels_help">Maximum number of channels that can be created (0 = unlimited)</div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-lg-2 col-form-label fw-bold" for="channel_template_cid">Channel template</label>
                                        <div class="col-lg-8">
                                            <select class="form-select" name="channel_template_cid" id="channel_template_cid">
                                                <option value="0" selected>None</option>
                                                @foreach($tsChannelTemplates->where('pid','=',0) as $tsChannelTemplate)
                                                    <option value="{{$tsChannelTemplate->cid}}" @if($tsChannelTemplate->cid === $job->channel_template_cid) selected @endif>{{$tsChannelTemplate->channel_name}}</option>
                                                    @foreach($tsChannelTemplates->where('pid','=',$tsChannelTemplate->cid) as $tsChannelTemplatePID)
                                                        <option value="{{$tsChannelTemplatePID->cid}}" @if($tsChannelTemplatePID->cid === $job->channel_template_cid) selected @endif>-{{$tsChannelTemplatePID->channel_name}}</option>
                                                    @endforeach
                                                @endforeach
                                            </select>
                                            <div class="form-text">Adopts the rights settings of the selected channel</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-actions" aria-expanded="false" aria-controls="panelsStayOpen-actions">
                                    Client action
                                </button>
                            </h2>
                            <div id="panelsStayOpen-actions" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <div class="row mb-3">
                                        <label class="col-lg-2 col-form-label fw-bold" for="action_user_id">Action</label>
                                        <div class="col-lg-8">
                                            <select class="form-select" name="action_user_id" id="action_user_id">
                                                @foreach($botActionUsers as $botActionUser)
                                                    <option value="{{$botActionUser->id}}" @if($botActionUser->id === $job->action_user_id) selected @endif>{{$botActionUser->action_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-lg-2 col-form-label fw-bold" for="channel_cgid">Channel group</label>
                                        <div class="col-lg-8">
                                            <select class="form-select" name="channel_cgid" id="channel_cgid">
                                                <option selected value="0">No action</option>
                                                @foreach($tsChannelGroups as $channelGroup)
                                                    <option value="{{$channelGroup->cgid}}" @if($channelGroup->cgid === $job->channel_cgid) selected @endif>{{$channelGroup->name}}</option>
                                                @endforeach
                                            </select>
                                            <div id="NotifyServerGroupSgidHelp" class="form-text">Channel group to be assigned to the client</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-notification" aria-expanded="false" aria-controls="panelsStayOpen-notification">
                                    Notification
                                </button>
                            </h2>
                            <div id="panelsStayOpen-notification" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <div class="row mb-3">
                                        <label class="col-lg-2 col-form-label fw-bold" for="is_notify_message_server_group">Notify group</label>
                                        <div class="col-lg-2">
                                            <select class="form-select" name="is_notify_message_server_group" id="is_notify_message_server_group">
                                                <option value="0" @if($job->is_notify_message_server_group == false) selected @endif>No</option>
                                                <option value="1" @if($job->is_notify_message_server_group == true) selected @endif>Yes</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-3">
                                            <select class="form-select" name="notify_option" id="notify_option">
                                                <option value="1">Text Message</option>
                                                <option value="2">Poke Message</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-5">
                                            <select class="form-select" name="notify_message_server_group_sgid" id="notify_message_server_group_sgid">
                                                <option selected value="0">None</option>
                                                @foreach($tsServerGroups as $tsServerGroup)
                                                    <option value="{{$tsServerGroup->sgid}}" @if($tsServerGroup->sgid === $job->notify_message_server_group_sgid) selected @endif>{{$tsServerGroup->name}}</option>
                                                @endforeach
                                            </select>
                                            <div id="NotifyServerGroupSgidHelp" class="form-text">Server group to be informed</div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-lg-2 col-form-label fw-bold" for="notify_message_server_group_message">Message</label>
                                        <div class="col-lg-10">
                                            <input class="form-control" type="text" name="notify_message_server_group_message" id="notify_message_server_group_message" value="{{$job->notify_message_server_group_message}}">
                                            <div id="NotifyServerGroupMessageHelp" class="form-text">Use placeholders to make your messages even more individual.
                                                <a class="form-text" href="#patternList" data-bs-toggle="modal">Show available placeholders</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
