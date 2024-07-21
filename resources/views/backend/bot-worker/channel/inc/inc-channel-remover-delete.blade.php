<div class="modal fade" id="ChannelRemoverDelete{{$remove->id}}" tabindex="-1" aria-labelledby="ChannelRemoverDeleteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="ChannelRemoverDeleteLabel">Job für Channel {{$remove->rel_ts3ChannelsRemover()->first()->channel_name}} löschen</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    Soll der Eintrag wirklich gelöscht werden?
                </p>
            </div>
            <div class="modal-footer">
                <form method="post" action="{{route('worker.delete.channelRemover')}}">
                    @csrf
                    <button type="submit" class="btn btn-danger" name="DeleteID" value="{{$remove->id}}">Löschen</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
            </div>
        </div>
    </div>
</div>
