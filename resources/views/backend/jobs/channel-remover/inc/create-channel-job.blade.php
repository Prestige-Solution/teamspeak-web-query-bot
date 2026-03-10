<div class="modal fade" id="CreateRemoverJob" tabindex="-1" aria-labelledby="CreateRemoverJobLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form class="was-validated" method="post" action="{{Route('channel.upsert.newChannelRemover')}}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5 fw-bold" id="CreateRemoverJobLabel">Add channel job</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label fw-bold" for="is_active">Status</label>
                        <div class="col-lg-4">
                            <select class="form-select" name="is_active" id="is_active" required>
                                <option value="0" selected>Inactive</option>
                                <option value="1">Active</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-3 col-form-label fw-bold" for="channel_cid">Select channel</label>
                        <div class="col-lg-8">
                            <select class="form-select" id="channel_cid" name="channel_cid" required>
                                <option selected disabled>Please choose</option>
                                @foreach($tsChannels->where('pid','=',0) as $tsChannel)
                                    <option value="{{$tsChannel->cid}}" >{{$tsChannel->channel_name}}</option>
                                    @foreach($tsChannels->where('pid','=',$tsChannel->cid) as $tsChannelPID)
                                        <option value="{{$tsChannelPID->cid}}">-{{$tsChannelPID->channel_name}}</option>
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
                            <input class="form-control" type="number" name="channel_max_seconds_empty" id="channel_max_seconds_empty" min="1" value="1" required>
                        </div>
                        <div class="col-lg-4">
                            <select class="form-select" name="channel_max_time_format" id="channel_max_time_format" aria-label="channel_max_time_format">
                                <option value="m">minute/s</option>
                                <option value="h">hour/s</option>
                                <option value="d" selected>day/s</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">Add</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
