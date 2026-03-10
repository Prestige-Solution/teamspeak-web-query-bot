<div class="modal fade" id="ServerReInit{{$server->id}}" tabindex="-1" aria-labelledby="ServerRecycleLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="ServerRecycleLabel">Reset {{$server->server_name}}</h1>
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
                <p>The server is then reinitialized</p>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <form method="post" action="{{route('serverConfig.update.serverInit')}}">
                    @csrf
                    <button type="submit" class="btn btn-danger" name="server_id" value="{{$server->id}}">Reset</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
