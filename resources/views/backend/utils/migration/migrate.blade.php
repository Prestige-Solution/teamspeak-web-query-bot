@extends('template')

@section('site-title')
    Server | {{config('app.name')}}
@endsection

@section('content')
<div class="container mt-3">
    <div class="row mb-2">
        <div class="col-lg-8">
            <h2 class="fs-3 fw-bold">Migration Tool</h2>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-lg-6 d-flex">
            <div class="card flex-fill">
                <div class="card-body">
                    <h5 class="card-title fw-bold">Steps</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">1. Setup your servers with <span class="fw-bold">query admin user</span> account</li>
                        <li class="list-group-item">2. Select source server</li>
                        <li class="list-group-item">3. Select target server</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6 d-flex">
            <div class="card flex-fill">
                <div class="card-body">
                    <h5 class="card-title fw-bold">Notes</h5>
                    <p class="card-text">
                        If you are using a Teamspeak 3 server, make sure you have configured and prepared it correctly. Instructions can be found here
                        <a href="https://github.com/Prestige-Solution/ts-x-php-framework/blob/main/doc/docker/make-ts3-ssh-compatible.md#setup-a-ssh_rsa_host_key" target="_blank">Setup a ssh_rsa_host_key</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <hr>
    @include('form-components.alertCustomError')
    @include('form-components.successCustom')
    <form action="{{route('migration.start.migration')}}" method="post">
        @csrf
        <div class="row mb-2">
            <div class="col-lg-12">
                <h2 class="fs-3 fw-bold">Settings</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-5">
                <label class="col-form-label fw-bold" for="source_server_id">Source Server</label>
                <select class="form-select form-select" id="source_server_id" name="source_server_id">
                    <option disabled selected>Select Server</option>
                    @foreach ($servers as $server)
                        <option value="{{$server->id}}">{{$server->server_name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-5">
                <label class="col-form-label fw-bold" for="target_server_id">Target Server</label>
                <select class="form-select form-select" id="target_server_id" name="target_server_id">
                    <option disabled selected>Select Server</option>
                    @foreach ($servers as $server)
                        <option value="{{$server->id}}">{{$server->server_name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 d-flex justify-content-end">
                <button class="btn btn-primary mt-auto" id="migrate-button" type="submit">Start Migration</button>
            </div>
        </div>
        <hr>
        <div class="row mb-2">
            <div class="col-lg-12">
                <h2 class="fs-3 fw-bold">Logs</h2>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-lg-12">
                @if(isset($logs))
                    <pre style="background:#111;color:#0f0;padding:1rem;border-radius:8px;overflow:auto;max-height:600px;">{{ trim($logs) }}</pre>
                @endif
            </div>
        </div>
    </form>
</div>
@endsection
