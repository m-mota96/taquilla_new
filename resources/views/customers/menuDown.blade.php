<nav class="navbar navbar-expand-lg bg-dark pt-1 pb-1">
    <div class="container">
        {{-- <a class="navbar-brand" href="#">Navbar</a> --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link text-white me-1" aria-current="page" href="{{route('estadisticas', $event->id)}}"><i class="fa-solid fa-chart-line"></i> Estadísticas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white me-1" aria-current="page" href="{{route('administrar_boletos', $event->id)}}"><i class="fa-solid fa-ticket"></i> Boletos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white me-1" aria-current="page" href="{{route('administrar_folios', $event->id)}}"><i class="fa-solid fa-list-ol"></i> Folios</a>
                </li>
                {{-- <li class="nav-item">
                    <a class="nav-link text-white me-1" aria-current="page" href=""><i class="fa-solid fa-users-gear"></i> Cajeros/as</a>
                </li> --}}
                <li class="nav-item">
                    <a class="nav-link text-white me-1" aria-current="page" href="{{route('caja_chica', $event->id)}}"><i class="fa-solid fa-box-open"></i> Caja chica</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white me-1" aria-current="page" href="{{route('cortes_de_caja', $event->id)}}"><i class="fa-solid fa-box-tissue"></i> Cortes de caja</a>
                </li>
                {{-- <li class="nav-item">
                <a class="nav-link text-white" href="#">Link</a>
                </li>
                <li class="nav-item dropdown">
                <a class="nav-link text-white dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Dropdown
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Action</a></li>
                    <li><a class="dropdown-item" href="#">Another action</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                </ul>
                </li> --}}
            </ul>
        </div>
    </div>
</nav>