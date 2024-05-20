<div class="modal fade" id="BannerUpload" tabindex="-1" aria-labelledby="BannerUploadLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="BannerUploadLabel">Banner hinzufügen</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input class="form-control" type="file" name="BannerUploadFile" id="BannerUploadFile" aria-label="BannerUploadFile">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                <button class="btn btn-primary" name="ServerID" value="{{$serverID}}">Upload</button>
            </div>
        </div>
    </div>
</div>