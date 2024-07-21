<div class="modal fade" id="JobDelete{{$job->id}}" tabindex="-1" aria-labelledby="JobDeleteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="JobDeleteLabel">Job für Channel {{$job->rel_channels()->first()->channel_name}} löschen</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    Soll der Job wirklich gelöscht werden?
                </p>
            </div>
            <div class="modal-footer">
                <form method="post" action="{{Route('channel.delete.channelJob')}}">
                    @csrf
                    <button type="submit" class="btn btn-danger" name="JobID" value="{{$job->id}}">Löschen</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
            </div>
        </div>
    </div>
</div>
