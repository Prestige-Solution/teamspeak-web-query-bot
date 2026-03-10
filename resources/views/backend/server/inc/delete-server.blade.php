<div class="modal fade" id="ServerDelete{{$server->id}}" tabindex="-1" aria-labelledby="ServerDeleteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="ServerDeleteLabel">Delete {{$server->server_name}}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="fw-bold text-danger">
                    The following settings are irrevocably deleted::
                </p>
                <ul>
                    <li>Banner configurations and templates</li>
                    <li>Channel configurations</li>
                    <li>Channel jobs</li>
                </ul>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <form method="post" action="{{route('serverConfig.delete.server')}}">
                    @csrf
                    <button type="submit" class="btn btn-danger" name="server_id" value="{{$server->id}}">Delete</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
