<div class="modal fade" id="BannerUpload" tabindex="-1" aria-labelledby="BannerUploadLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="{{Route('banner.create.uploadedTemplate')}}" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fw-bold fs-5" id="BannerUploadLabel">Banner Template hinzufügen</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="BannerName">Banner Name</label>
                            <input class="form-control" type="text" name="BannerName" id="BannerName" placeholder="Mein Banner">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="BannerUploadFile">Datei auswählen</label>
                            <input class="form-control" type="file" name="BannerUploadFile" id="BannerUploadFile" aria-label="BannerUploadFile">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button class="btn btn-primary" type="submit" name="ServerID" value="{{$serverID}}">Upload</button>
                </div>
            </div>
        </form>
    </div>
</div>
