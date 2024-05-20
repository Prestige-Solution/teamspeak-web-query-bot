<div class="modal fade" id="patternList" tabindex="-1" aria-labelledby="patternListLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="patternListLabel">Verfügbare Platzhalter</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Platzhalter</th>
                        <th scope="col">Ausgabe</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>{client-name}</td>
                        <td>Name des Client</td>
                    </tr>
                    <tr>
                        <td>{channel-name}</td>
                        <td>Name des Channels</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
            </div>
        </div>
    </div>
</div>
