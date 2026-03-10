<nav class="navbar navbar-expand-lg navbar-dark bg-dark" aria-label="Tenth navbar example">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="{{Route('public.view.login')}}">Web Query Bot</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarDashboard" aria-controls="navbarDashboard" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-md-center" id="navbarDashboard">
            <ul class="navbar-nav">
            @auth()
                @if(\Illuminate\Support\Facades\Auth::user()->default_server_id == 0)
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{Route('serverConfig.view.serverList')}}">Server</a>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{Route('backend.view.dashboard')}}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{Route('backend.view.botControlCenter')}}">Bot Control Center</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Channel Management
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{Route('channel.view.channelJobs')}}"><i class="fa-solid fa-bars-staggered"></i> Channel Creator</a></li>
                            <li><a class="dropdown-item" href="{{Route('channel.view.listChannelRemover')}}"><i class="fa-solid fa-list-check"></i> Channel Remover</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Client Management
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{Route('worker.view.createOrUpdateAfkWorker')}}"><i class="fa-solid fa-user-minus"></i> AFK Settings</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{Route('banner.view.listBanner')}}">Banner Creator</a>
                    </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Utils
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{Route('migration.view.migrationSettings')}}"><i class="fa-solid fa-arrows-spin"></i> Migration Tool</a></li>
                            </ul>
                        </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{Route('serverConfig.view.serverList')}}">Server</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Settings
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{Route('worker.view.badNames')}}"><i class="fa-solid fa-users-slash"></i> Bad Nicknames</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{Route('worker.view.upsertPoliceWorker')}}"><i class="fa-solid fa-gear"></i> Bot Settings</a></li>
                            <li><a class="dropdown-item" href="{{Route('backend.view.botLogs')}}"><i class="fa-solid fa-clock-rotate-left"></i> Bot Logs</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{Route('backend.view.changePassword')}}"><i class="fa-solid fa-key"></i> Change Password</a></li>
                        </ul>
                    </li>
                @endif
                    <li class="nav-item">
                        <a class="nav-link text-warning fw-bold" aria-current="page" href="{{Route('logout')}}">Logout</a>
                    </li>
            @endauth
            </ul>
        </div>
    </div>
</nav>
