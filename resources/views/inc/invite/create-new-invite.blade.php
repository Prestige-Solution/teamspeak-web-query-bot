<div class="modal fade" id="CreateNewInviteModal" tabindex="-1" aria-labelledby="CreateNewInviteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{Route('backend.create.newInvite')}}">
                <div class="modal-header">
                    <h1 class="modal-title fs-5 fw-bold" id="CreateNewInviteModalLabel">Einladungscode erstellen</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="Email">E-Mail Adresse</label>
                        <input class="form-control" type="email" id="Email" name="Email" placeholder="name@example.de" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-primary">Erstellen</button>
                </div>
            </form>
        </div>
    </div>
</div>
