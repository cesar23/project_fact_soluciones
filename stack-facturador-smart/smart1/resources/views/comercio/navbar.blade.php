<!-- resources/views/comercio/navbar.blade.php -->

<nav class="navbar navbar-expand-lg custom-navbar compact-navbar mb-0">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('tenant.comercios.records') }}">
            @if($logo_url)
                <img src="{{ $logo_url }}" alt="Logo" class="logo">
            @else
                <img src="{{asset('logo/tulogo.png')}}" alt="Logo" class="logo">
            @endif
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle custom-nav-link" href="#" id="navbarDropdown"
                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Categorías
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        @foreach ($tags as $tag)
                            <li><a class="dropdown-item"
                                    href="{{ route('comercio.filtrar', $tag->id) }}">{{ $tag->name }}</a></li>
                        @endforeach
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link custom-nav-link active" aria-current="page"
                        href="{{ route('tenant.comercios.records') }}">Inicio</a>
                </li>

            </ul>
            <form class="navbar-search d-flex">
                <input id="search" type="search" class="form-control me-2" placeholder="Buscar...">
                <button id="search-button" class="btn btn-outline-light">
                    <i class="bi bi-search"></i>
                </button>
                <div id="searchResults"></div>
            </form>
        </div>
    </div>
</nav>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        let typingTimer;
        let doneTypingInterval = 1000; // 300 ms de intervalo

        function makeSearch() {
            var query = $('#search').val();
                let url = "{{ route('tenant.comercios.records') }}?search=" + query;
                console.log(url);
                window.location.href = url;
            
        }

        // Evento click del botón
        $('#search-button').on('click', function(e) {
            e.preventDefault();
            makeSearch();
        });

        // Evento Enter en el input
        $('#search').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                makeSearch();
            }
        });

        // Para ocultar los resultados cuando se hace clic fuera
        // $(document).on('click', function(e) {
        //     if (!$(e.target).closest('.navbar-search').length) {
        //         $('#searchResults').removeClass('show');
        //     }
        // });
    });
</script>
