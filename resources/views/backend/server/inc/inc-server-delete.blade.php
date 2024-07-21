<div class="modal fade" id="ServerDelete{{$server->id}}" tabindex="-1" aria-labelledby="ServerDeleteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="ServerDeleteLabel">{{$server->server_name}} löschen</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    <span class="fw-bold text-danger">Warnung:</span><br>
                    Wenn der Server gelöscht wird, werden alle Einstellungen entfernt.
                    Alle Banner Konfigurationen und Vorlagen werden ebenfalls gelöscht.
                </p>
            </div>
            <div class="modal-footer">
                <form method="post" action="{{route('serverConfig.delete.server')}}">
                    @csrf
                    <button type="submit" class="btn btn-danger" name="ServerID" value="{{$server->id}}">Jetzt löschen</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
            </div>
        </div>
    </div>
</div>
