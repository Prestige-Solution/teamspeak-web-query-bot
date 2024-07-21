<div class="modal fade" id="ServerReInit{{$server->id}}" tabindex="-1" aria-labelledby="ServerRecycleLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="ServerRecycleLabel">{{$server->server_name}} zurücksetzen</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    <span class="fw-bold text-danger">Warnung:</span><br>
                    Wenn der Server zurückgesetzt wird, werden alle Einstellungen entfernt und vom Teamspeak Server neu geladen.
                    Gesetzte Einstellungen müssen neu gesetzt werden. Alle Banner Konfigurationen und Vorlagen werden ebenfalls entfernt.
                </p>
            </div>
            <div class="modal-footer">
                <form method="post" action="{{route('serverConfig.update.serverInit')}}">
                    @csrf
                    <button type="submit" class="btn btn-danger" name="ServerID" value="{{$server->id}}">Zurücksetzen</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
            </div>
        </div>
    </div>
</div>
