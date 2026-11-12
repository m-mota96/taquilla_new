<nav class="navbar navbar-expand-lg bg-dark pt-1 pb-1">
    <div class="container">
        {{-- <a class="navbar-brand" href="#">Navbar</a> --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link text-white me-1" aria-current="page" href="{{route('venta', $event->id)}}"><i class="fa-solid fa-dollar-sign"></i> Vender boletos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white me-1" aria-current="page" href="{{route('ver_ventas', $event->id)}}"><i class="fa-solid fa-file-invoice-dollar"></i> Ver ventas</a>
                </li>
            </ul>
        </div>
    </div>
</nav>