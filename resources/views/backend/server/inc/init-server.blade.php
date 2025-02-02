<div class="modal fade" id="ServerReInit{{$server->id}}" tabindex="-1" aria-labelledby="ServerRecycleLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="ServerRecycleLabel">{{$server->server_name}} zurücksetzen</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="fw-bold text-danger">
                    Folgende Einstellungen werden unwiderruflich gelöscht:
                </p>
                <ul>
                    <li>Banner Konfigurationen sowie Vorlagen</li>
                    <li>Channel Konfigurationen</li>
                    <li>Channel Jobs (Create and Remove)</li>
                </ul>
                <p>Der Server wird anschließend neu initialisiert</p>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <form method="post" action="{{route('serverConfig.update.serverInit')}}">
                    @csrf
                    <button type="submit" class="btn btn-danger" name="server_id" value="{{$server->id}}">Zurücksetzen</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
            </div>
        </div>
    </div>
</div>
