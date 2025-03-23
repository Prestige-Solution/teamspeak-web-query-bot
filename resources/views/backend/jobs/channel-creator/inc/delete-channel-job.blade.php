<div class="modal fade" id="JobDelete{{$job->id}}" tabindex="-1" aria-labelledby="JobDeleteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="JobDeleteLabel">Delete job {{$job->rel_channels()->first()->channel_name}}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    If the job for channel <span class="fw-bold">{{$job->rel_channels()->first()->channel_name}}</span> is to be permanently removed?
                </p>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <form method="post" action="{{Route('channel.delete.channelJob')}}">
                    @csrf
                    <button type="submit" class="btn btn-danger" name="id" value="{{$job->id}}">Remove</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
