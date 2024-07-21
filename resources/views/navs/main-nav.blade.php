<nav class="navbar navbar-expand-lg navbar-dark bg-dark" aria-label="Tenth navbar example">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{Route('public.view.login')}}">PS-Bot</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarDashboard" aria-controls="navbarDashboard" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-md-center" id="navbarDashboard">
            <ul class="navbar-nav">
            @auth()
                @if(\Illuminate\Support\Facades\Auth::user()->server_id == 0)
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{Route('start.view.dashboard')}}">Start</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{Route('start.view.useInviteCode')}}">Einladungscode verwenden</a>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{Route('backend.view.botControlCenter')}}">Bot Control Center</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Funktionen
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{Route('channel.view.listChannel')}}">Channel Jobs</a></li>
                            <li><a class="dropdown-item" href="{{Route('channel.view.createJobChannel')}}">Channel Job erstellen</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{Route('worker.view.listChannelRemover')}}">Channel Remover</a></li>
                            <li><a class="dropdown-item" href="{{Route('worker.view.upsertChannelRemover')}}">Channel Remover erstellen</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{Route('banner.view.listBanner')}}">Banner Creator</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Einstellungen
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{Route('worker.view.createOrUpdateAfkWorker')}}">AFK Einstellungen</a></li>
                            <li><a class="dropdown-item" href="{{Route('worker.view.upsertPoliceWorker')}}">Bot Einstellungen</a></li>
                            <li><a class="dropdown-item" href="{{Route('backend.view.badNames')}}">Bad Nicknames</a></li>
                            <li><hr class="dropdown-divider"></li>

                            <li><a class="dropdown-item" href="#">Nutzer Einstellungen</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{Route('serverConfig.view.serverList')}}">Serverliste</a>
                    </li>
                @endif
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Mein Profil
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{Route('backend.view.changePassword')}}">Passwort ändern</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{Route('backend.view.botLogs')}}">Bot Logs</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item fw-bold" href="{{Route('logout')}}">Logout</a></li>
                        </ul>
                    </li>
            @endauth
            </ul>
        </div>
    </div>
</nav>
