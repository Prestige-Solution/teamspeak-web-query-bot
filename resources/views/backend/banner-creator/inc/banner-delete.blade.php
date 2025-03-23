<div class="modal fade" id="BannerDelete{{$banner->id}}" tabindex="-1" aria-labelledby="BannerDeleteLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="{{ Route('banner.delete.banner') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fw-bold fs-5" id="BannerDeleteLabel">Template löschen</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>
                        Soll der Banner <span class="fw-bold">"{{ $banner->banner_name }}"</span> und alle Konfigurationen wirklich gelöscht werden?
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button class="btn btn-primary" type="submit" name="BannerID" value="{{$banner->id}}">Löschen</button>
                </div>
            </div>
        </form>
    </div>
</div>
