<div class="modal fade" id="CreateBadName" tabindex="-1" aria-labelledby="CreateBadName" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="CreateBadNameLabel">Bad Name hinzufügen</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="{{Route('backend.create.newBadName')}}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="NameDescription">Beschreibung</label>
                        <input class="form-control" type="text" id="NameDescription" name="NameDescription">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="ProofOption">Option</label>
                        <select class="form-select" id="ProofOption" name="ProofOption">
                            <option selected disabled>Bitte wählen</option>
                            <option value="1">Enthält</option>
                            <option value="2">Regular Expression</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="Value">Wert</label>
                        <input class="form-control" type="text" id="Value" name="Value">
                        <div id="emailHelp" class="form-text">
                            Example for Regular Expression: /[a@4]dm[i!1]n[i!1](s|2|s|22|ß)tr[a@4]t[o0]/i
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button class="btn btn-primary" name="ServerID" value="{{\Illuminate\Support\Facades\Auth::user()->server_id}}">Hinzufügen</button>
                </div>
            </form>
        </div>
    </div>
</div>