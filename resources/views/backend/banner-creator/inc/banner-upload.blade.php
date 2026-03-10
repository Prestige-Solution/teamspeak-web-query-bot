<div class="modal fade" id="BannerUpload" tabindex="-1" aria-labelledby="BannerUploadLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="{{Route('banner.create.uploadedTemplate')}}" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fw-bold fs-5" id="BannerUploadLabel">Add Banner Template</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="banner_name">Banner Name</label>
                            <input class="form-control" type="text" name="banner_name" id="banner_name" placeholder="My Banner">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="banner_original_file_name">File</label>
                            <input class="form-control" type="file" name="banner_original_file_name" id="banner_original_file_name" aria-label="banner_original_file_name">
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button class="btn btn-primary" type="submit">Upload</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>
