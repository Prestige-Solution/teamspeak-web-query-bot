<div class="modal fade" id="CreateServer" tabindex="-1" aria-labelledby="CreateServerLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form class="was-validated" method="post" action="{{Route('serverConfig.create.server')}}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5 fw-bold" id="CreateServerLabel">Server hinzufügen:</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <label class="col-lg-3 col-form-label fw-bold" for="server_name">Servername:</label>
                        <div class="col-lg-9">
                            <input class="form-control" type="text" name="server_name" id="server_name" placeholder="Servername" required>
                            <div class="invalid-feedback">
                                Bitte gib einen Servernamen ein.
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <label class="col-lg-3 col-form-label fw-bold" for="server_ip">IP Adresse:</label>
                        <div class="col-lg-9">
                            <input class="form-control" type="text" name="server_ip" id="server_ip" placeholder="IPV4 or IPV6 or DNS Address" required>
                            <div class="invalid-feedback">
                                Beispiele: IPV4: 127.0.0.1 | IPV6: 0:0:0:0:0:0:0:1 or ::1 | DNS: ts3.example.com
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <label class="col-lg-3 col-form-label fw-bold" for="qa_name">Query Admin:</label>
                        <div class="col-lg-9">
                            <input class="form-control" type="text" name="qa_name" id="qa_name" placeholder="Query Admin Name" maxlength="30" pattern="^((?!serveradmin).)*$" required>
                            <div class="invalid-feedback">
                                Der Name darf nicht länger als 30 Zeichen sein. Die Verwendung des Accounts "serveradmin" wird nicht unterstützt.
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <label class="col-lg-3 col-form-label fw-bold" for="qa_pw">Query Admin Passwort:</label>
                        <div class="col-lg-9">
                            <input class="form-control" type="password" name="qa_pw" id="qa_pw" placeholder="Query Admin Password" required>
                            <div class="invalid-feedback">
                                Gib bitte das Passwort des Query Nutzers ein.
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <label class="col-lg-3 col-form-label fw-bold" for="server_query_port">Query Port:</label>
                        <div class="col-lg-9">
                            <input class="form-control" type="text" name="server_query_port" id="server_query_port" placeholder="RAW 10011 | SSH 10022">
                            <div class="form-text">
                                Trage hier einen spezifisch konfigurierte Query Port ein. Andernfalls bitte das Feld frei lassen
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <label class="col-lg-3 col-form-label fw-bold" for="server_port">Server Port:</label>
                        <div class="col-lg-9">
                            <input class="form-control" type="text" name="server_port" id="server_port" placeholder="9987">
                            <div class="form-text">
                                Trage hier einen spezifisch konfigurierte Server Port ein. Als Standard wird 9987 verwendet.
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <label class="col-lg-3 col-form-label fw-bold" for="mode">Verbindungsmodus:</label>
                        <div class="col-lg-9">
                            <select class="form-select" name="mode" id="mode">
                                <option value="{{\App\Models\ts3Bot\ts3ServerConfig::TS3ConnectModeRAW}}" selected>RAW</option>
                                <option value="{{\App\Models\ts3Bot\ts3ServerConfig::TS3ConnectModeSSH}}">SSH</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <label class="col-lg-3 col-form-label fw-bold" for="qa_nickname">Query Nickname:</label>
                        <div class="col-lg-9">
                            <input class="form-control" type="text" name="qa_nickname" id="qa_nickname" placeholder="web-query-bot" maxlength="11">
                            <div class="form-text">
                                Wähle einen Namen mit dem sich der Bot auf deinen Teamspeak verbinden soll. Der Name darf nicht länger als 11 Zeichen sein.
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <label class="col-lg-3 col-form-label fw-bold" for="description">Beschreibung:</label>
                        <div class="col-lg-9">
                            <textarea class="form-control" name="description" id="description" rows="3" maxlength="255" placeholder="Hier ist Platz für deine Notizen"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-primary">Speichern & Initialisieren</button>
                </div>
            </div>
        </form>
    </div>
</div>
