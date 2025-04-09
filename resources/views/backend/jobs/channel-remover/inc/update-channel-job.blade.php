<div class="modal fade" id="UpdateRemoverJob{{$job->id}}" tabindex="-1" aria-labelledby="UpdateRemoverJob{{$job->id}}Label" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form class="was-validated" method="post" action="{{Route('channel.upsert.newChannelRemover')}}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5 fw-bold" id="UpdateRemoverJob{{$job->id}}Label">Update channel job {{$job->rel_channels()->first()->channel_name}}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label fw-bold" for="is_active">Status</label>
                        <div class="col-lg-4">
                            <select class="form-select" name="is_active" id="is_active" required>
                                <option value="0" @if($job->is_active == false) selected @endif>Inactive</option>
                                <option value="1" @if($job->is_active == true) selected @endif>Active</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label fw-bold" for="channel_cid">Select channel</label>
                        <div class="col-lg-8">
                            <select class="form-select" id="channel_cid" name="channel_cid">
                                <option selected disabled>Please choose</option>
                                @foreach($tsChannels->where('pid','=',0) as $tsChannel)
                                    <option value="{{$tsChannel->cid}}" @if($tsChannel->cid === $job->channel_cid) selected @else disabled @endif>{{$tsChannel->channel_name}}</option>
                                    @foreach($tsChannels->where('pid','=',$tsChannel->cid) as $tsChannelPID)
                                        <option value="{{$tsChannelPID->cid}}" @if($tsChannelPID->cid === $job->channel_cid) selected @else disabled @endif>-{{$tsChannelPID->channel_name}}</option>
                                    @endforeach
                                @endforeach
                            </select>
                            <div class="form-text">
                                All sub-channels of the selected channel are deleted
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label fw-bold" for="channel_max_seconds_empty">Inactive since</label>
                        <div class="col-lg-4">
                            <input class="form-control" type="number" name="channel_max_seconds_empty" id="channel_max_seconds_empty" min="1" required
                                   @if($job->channel_max_time_format == 'm')
                                       value="{{$job->channel_max_seconds_empty / 60}}"
                                   @endif
                                   @if($job->channel_max_time_format == 'h')
                                       value="{{$job->channel_max_seconds_empty / (60 * 60)}}"
                                   @endif
                                   @if($job->channel_max_time_format == 'd')
                                       value="{{$job->channel_max_seconds_empty / (24*60*60)}}"
                                   @endif>
                        </div>
                        <div class="col-lg-4">
                            <select class="form-select" name="channel_max_time_format" id="channel_max_time_format" aria-label="channel_max_time_format">
                                <option value="m" @if($job->channel_max_time_format === "m") selected @endif>minute/s</option>
                                <option value="h" @if($job->channel_max_time_format === "h") selected @endif>hour/s</option>
                                <option value="d" @if($job->channel_max_time_format === "d") selected @endif>day/s</option>
                            </select>
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
