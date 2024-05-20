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
                            <li><a class="dropdown-item" href="{{Route('channel.view.channelList')}}">Channel Jobs</a></li>
                            <li><a class="dropdown-item" href="{{Route('channel.view.createOrUpdateJobChannel',['server_id'=>\Illuminate\Support\Facades\Auth::user()->server_id])}}">Channel Job erstellen</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{Route('worker.view.listChannelRemover',['server_id'=>\Illuminate\Support\Facades\Auth::user()->server_id])}}">Channel Remover</a></li>
                            <li><a class="dropdown-item" href="{{Route('worker.view.createOrUpdateChannelRemover',['server_id'=>\Illuminate\Support\Facades\Auth::user()->server_id])}}">Channel Remover erstellen</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{Route('banner.view.listBanner',['server_id'=>\Illuminate\Support\Facades\Auth::user()->server_id])}}">Banner Creator</a></li>
                        </ul>
                    </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Einstellungen
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{Route('worker.view.createOrUpdateAfkWorker',['server_id'=>\Illuminate\Support\Facades\Auth::user()->server_id])}}">AFK Einstellungen</a></li>
                                <li><a class="dropdown-item" href="{{Route('worker.view.createOrUpdatePoliceWorker',['server_id'=>\Illuminate\Support\Facades\Auth::user()->server_id])}}">Bot Einstellungen</a></li>
                                <li><a class="dropdown-item" href="{{Route('backend.view.badNames',['server_id'=>\Illuminate\Support\Facades\Auth::user()->server_id])}}">Bad Name Liste</a></li>
                                @if(\Illuminate\Support\Facades\Auth::user()->server_id != 0)
                                    @if(\Illuminate\Support\Facades\Auth::user()->server_owner == true)
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{Route('backend.view.serverList')}}">Serverliste</a></li>
                                <li><a class="dropdown-item" href="#">Nutzer Einstellungen</a></li>
                                    @endif
                                @endif
                            </ul>
                        </li>
                @endif
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Mein Profil
                        </a>
                        <ul class="dropdown-menu">
                            @if(\Illuminate\Support\Facades\Auth::user()->server_id != 0)
                                @if(\Illuminate\Support\Facades\Auth::user()->server_owner == true)
                                    <li><a class="dropdown-item" href="{{Route('backend.view.changePassword',['server_id'=>\Illuminate\Support\Facades\Auth::user()->server_id])}}">Passwort ändern</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                                <li><a class="dropdown-item" href="{{Route('backend.view.botLogs')}}">Bot Logs</a></li>
                                <li><hr class="dropdown-divider"></li>
                            @endif
                            <li><a class="dropdown-item fw-bold" href="{{Route('logout')}}">Logout</a></li>
                        </ul>
                    </li>
            @endauth
            </ul>
        </div>
    </div>
</nav>
