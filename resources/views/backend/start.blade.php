@extends('template')

@section('site-title')
    Dashboard | Derra Bot
@endsection

@section('content')
    <div class="container-fluid bg-dark">
        <div class="p-5 mb-4 rounded-3 text-white">
            <div class="container py-5">
                <div class="row">
                    <div class="col-lg-6">
                        <h1 class="display-5 fw-bold">Willkommen beim PS-Bot</h1>
                    </div>
                    <div class="col-lg-6 d-flex align-items-center justify-content-center">
                        <a href="{{Route('backend.view.createOrUpdateServer')}}" class="btn btn-primary fs-5 me-3">Server hinzufügen</a>
                        <a href="{{Route('start.view.useInviteCode')}}" class="btn btn-primary fs-5">Einladungscode</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container mt-2">
        <div class="row mt-3">
            <div class="col-lg-12">
                <h3 class="fs-2 fw-bold">Schnelleinstieg</h3>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="accordion" id="accordionExample">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            Schritt 1: Registrierung abschließen & Server hinzufügen
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            Wir haben dir eine Willkommensnachricht mit deinem Registrierungslink zugeschickt. Nach der Bestätigung deiner E-Mail-Adresse kannst
                            du über <b>Server hinzufügen</b> deinen Teamspeak hinzufügen und das Bot Control Center aufrufen.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            Schritt 2: Das Control Center zur Whitelist hinzufügen
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            Damit der PS-Bot mit deinem Server interagieren kann muss dieser in der Teamspeak Server Whitelist eingetragen werden. Die IP lautet 80.147.25.186
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            Schritt 3: Einrichtung Bot Identität und Bot Berechtigung
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            Wir wollen das Ihr den Bot und euren Server sicher betreiben könnt. Dazu ist es notwendig, dass der Bot eine eigene Identität sowie eine eigene
                            Servergruppe erhält. Die Nutzung des Standardserver Query Admins wird unterbunden.<br><br>
                            Wir haben für euch aber ein ausführliches Tutorial erstellt. Die Einrichtung des PS-Bots ist damit schnell erledigt.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection