<nav class="navbar navbar-expand-lg bg-black">
    <div class="container">
        {{-- <a class="navbar-brand" href="#">Navbar</a> --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 w-50">
                <li class="nav-item">
                    <a class="nav-link text-white me-4" aria-current="page" href="{{route('home')}}">Mis eventos</a>
                </li>
                @if ($user->getRoleNames()[0] == 'customer')
                    <li class="nav-item">
                        <a class="nav-link text-white me-4" aria-current="page" href="{{route('administrar_cajeros')}}">Cajeros</a>
                    </li>
                @endif
                {{-- @if ($user->getRoleNames()[0] == 'cashier')
                    <li class="nav-item">
                        <a class="nav-link text-white me-4" aria-current="page" href="{{route('ver_ventas', $event->id)}}">Ventas</a>
                    </li>
                @endif --}}
            </ul>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 w-50 justify-content-end">
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
                <li class="nav-item dropdown">
                    <a id="navbarDropdown" class="nav-link text-white dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        {{ Auth::user()->name }}
                    </a>

                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                                         document.getElementById('logout-form').submit();">
                            Cerrar sesión
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>