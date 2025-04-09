<div class="modal fade" id="BannerDelete{{$banner->id}}" tabindex="-1" aria-labelledby="BannerDeleteLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="{{ Route('banner.delete.banner') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fw-bold fs-5" id="BannerDeleteLabel">Delete Template</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>
                        Should the banner <span class="fw-bold">"{{ $banner->banner_name }}"</span> and all configurations really be deleted?
                    </p>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button class="btn btn-danger" type="submit" name="id" value="{{$banner->id}}">Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>
