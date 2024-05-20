@extends('template')

@section('site-title')
    Startseite | PS-Bot
@endsection

@section('custom-css')
    <link href="{{asset('css/font-awesome/css/fontawesome.css')}}" rel="stylesheet">
    <link href="{{asset('css/font-awesome/css/brands.css')}}" rel="stylesheet">
    <link href="{{asset('css/font-awesome/css/solid.css')}}" rel="stylesheet">
@endsection

@section('content')
    <div class="container-fluid bg-dark">
        <div class="p-5 mb-4 rounded-3 text-white">
            <div class="container py-5">
                <div class="row">
                    <div class="col-lg-10">
                        <h1 class="display-5 fw-bold">PS-Bot</h1>
                        <p class="fs-4">
                            Mit dem PS-Bot steuerst und überwachst du deinen Teamspeak3 Server. Durch verschiedene Worker kannst du nahezu alles automatisieren.
                        </p>
                    </div>
                    <div class="col-lg-2 d-flex align-items-center justify-content-center">
                        <img class="img-fluid" src="{{asset('storage/img/ps-bot.png')}}" alt="PS-Bot Logo" oncontextmenu="return false" style="width: 18rem;">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="mt-3">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 d-flex justify-content-center align-items-stretch">
                        <div class="col-3">
                            <i class="fa-solid fa-robot fa-3x"></i>
                        </div>
                        <div class="col-9">
                            <p><span class="fw-bold">PS-Bot</span><br>
                                <small class="text-muted">Der PS-Bot wacht über deinen Server und reagiert auf verschiedene Ereignisse</small>
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-4 d-flex justify-content-center align-items-stretch">
                        <div class="col-3">
                            <i class="fa-solid fa-star fa fa-3x"></i>
                        </div>
                        <div class="col-9">
                            <p><span class="fw-bold">Features</span><br>
                                <small class="text-muted">Wir entwickeln stetig neue Features. Teil uns gerne eure wünsche.</small>
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-4 d-flex justify-content-center align-items-stretch">
                        <div class="col-3">
                            <i class="fa-solid fa-users fa-3x"></i>
                        </div>
                        <div class="col-9">
                            <p><span class="fw-bold">Multi User</span><br>
                                <small class="text-muted">Lade Freunde oder Kollegen ein welche deinen Server administrieren dürfen.</small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr class="py-3 bg-secondary">
    <div class="container mt-2">
        <div class="row g-5 py-5">
            <div class="col-lg-6">
                <h3 class="display-5 fw-bold lh-1 mb-3">Banner Creator</h3>
                <p class="lead">
                    Lade deinen "blanko" Banner hoch und kreiere dynamische Banner. Wähle aus Open Source Schriftarten sowie frei wählbaren Farben.
                    Wähle aus einer Vielzahl von möglichen Server variablen, welche auf dem Banner angezeigt werden sollen und bestimme deine Rotationszeit selbst.
                </p>
            </div>
            <div class="col-lg-6">
                <h3 class="display-5 fw-bold lh-1 mb-3">Sicherheit</h3>
                <p class="lead">
                    Aus Sicherheitsgründen verbieten wir die Nutzung des Query Admins. Keine Sorge, wir haben ein Tutorial wie Ihr einen
                    Query Account anlegt und eine Übersicht welche Rechte benötigt werden. Alle Passwörter und Webhooks werden verschlüsselt gespeichert.
                </p>
            </div>
        </div>
    </div>
    <div class="container mt-2">
        <div class="row g-5 py-5">
            <div class="col-lg-6">
                <h3 class="display-5 fw-bold lh-1 mb-3">Bot & Worker Konzept</h3>
                <p class="lead">
                    Durch unser Bot & Worker Konzept haben wir die maximale Flexibilität einen Server zu verwalten und stetig neue Features zu
                    implementieren. Somit könnt Ihr nicht nur euren Server überwachen, sondern auch ob euer Bot noch arbeitet.
                </p>
            </div>
            <div class="col-lg-6">
                <h3 class="display-5 fw-bold lh-1 mb-3">Logging</h3>
                <p class="lead">
                    Sollte der Bot mal nicht so wollen wie Ihr das wollt dann könnt Ihr auf ein umfangreiches Logging zurückgreifen.
                    Ihr könnt somit erkennen, ob es Verbindungsabbrüche oder fehlende Berechtigungen gibt.
                </p>
            </div>
        </div>
    </div>
@endsection