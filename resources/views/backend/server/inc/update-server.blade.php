<div class="modal fade" id="UpdateServer{{ $server->id }}" tabindex="-1" aria-labelledby="UpdateServer{{ $server->id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form class="was-validated" method="post" action="{{Route('serverConfig.update.server')}}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5 fw-bold" id="UpdateServer{{ $server->id }}Label">Edit Server "{{ $server->server_name }}"</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <label class="col-lg-3 col-form-label fw-bold" for="server_name">Servername</label>
                        <div class="col-lg-9">
                            <input class="form-control" type="text" name="server_name" id="server_name" value="{{ $server->server_name }}" placeholder="Servername" required>
                            <div class="invalid-feedback">
                                Please enter a server name.
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <label class="col-lg-3 col-form-label fw-bold" for="server_ip">IP adresse</label>
                        <div class="col-lg-9">
                            <input class="form-control" type="text" name="server_ip" id="server_ip" value="{{ $server->server_ip }}" placeholder="IPv4 or IPv6 or DNS address" required>
                            <div class="invalid-feedback">
                                Example: IPv4: 127.0.0.1 | IPv6: 0:0:0:0:0:0:0:1 or ::1 | DNS: ts3.example.com
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <label class="col-lg-3 col-form-label fw-bold" for="qa_name">Query admin</label>
                        <div class="col-lg-9">
                            <input class="form-control" type="text" name="qa_name" id="qa_name" value="{{ $server->qa_name }}" placeholder="Query admin name" pattern="^((?!serveradmin).)*$" maxlength="30" required>
                            <div class="invalid-feedback">
                                The name must not be longer than 30 characters. The use of the “serveradmin” account is not supported.
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <label class="col-lg-3 col-form-label fw-bold" for="qa_pw">Query admin password</label>
                        <div class="col-lg-9">
                            <input class="form-control" type="password" name="qa_pw" id="qa_pw" placeholder="Query admin password" required>
                            <div class="invalid-feedback">
                                Please enter the password of the query user.
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <label class="col-lg-3 col-form-label fw-bold" for="server_query_port">Query port</label>
                        <div class="col-lg-9">
                            <input class="form-control" type="text" name="server_query_port" id="server_query_port" {{ $server->server_query_port }} placeholder="RAW 10011 | SSH 10022">
                            <div class="form-text">
                                Enter a specifically configured query port here. Otherwise, please leave the field blank
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <label class="col-lg-3 col-form-label fw-bold" for="server_port">Server port</label>
                        <div class="col-lg-9">
                            <input class="form-control" type="text" name="server_port" id="server_port" value="{{ $server->server_port }}" placeholder="9987">
                            <div class="form-text">
                                Enter a specifically configured server port here. The default is 9987.
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <label class="col-lg-3 col-form-label fw-bold" for="mode">Connection mode</label>
                        <div class="col-lg-9">
                            <select class="form-select" name="mode" id="mode">
                                <option value="{{\App\Models\ts3Bot\ts3ServerConfig::TS3ConnectModeRAW}}" @if($server->mode === \App\Models\ts3Bot\ts3ServerConfig::TS3ConnectModeRAW) selected @endif>RAW</option>
                                <option value="{{\App\Models\ts3Bot\ts3ServerConfig::TS3ConnectModeSSH}}" @if($server->mode === \App\Models\ts3Bot\ts3ServerConfig::TS3ConnectModeSSH) selected @endif>SSH</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <label class="col-lg-3 col-form-label fw-bold" for="qa_nickname">Query nickname</label>
                        <div class="col-lg-9">
                            <input class="form-control" type="text" name="qa_nickname" id="qa_nickname" value="{{ $server->qa_nickname }}" placeholder="web-query-bot" maxlength="11">
                            <div class="form-text">
                                Choose a name with which the bot should connect to your Teamspeak. The name must not be longer than 11 characters.
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <label class="col-lg-3 col-form-label fw-bold" for="description">Description</label>
                        <div class="col-lg-9">
                            <textarea class="form-control" name="description" id="description" rows="3" maxlength="255" placeholder="Your notes">{{ $server->description }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary" name="server_id">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
