<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $trade_name }}</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('css/comercio.css') }}" rel="stylesheet">
    @if ($favicon)
        <link rel="shortcut icon" type="image/png" href="{{ asset('storage/uploads/favicons/'.$favicon) }}" />
    @else
    <link rel="icon" type="image/x-icon" href="{{ asset('/images/fondos/seguridad.png') }}">
    @endif
</head>
<body>
    <!-- Navbar -->
    @include('comercio.navbar')

    <!-- Carousel -->
    @if($promotions->isNotEmpty())
        <div id="promotionCarousel" class="carousel slide mb-0 banner-carousel" data-bs-ride="carousel">
            <div class="carousel-inner">
                @foreach($promotions as $index => $promotion)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <img src="{{ $promotion->image_url }}" class="d-block w-100" alt="Promotion Image">
                    </div>
                @endforeach
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#promotionCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#promotionCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    @endif

    <!-- Contenido principal -->
    <div class="container-main">

    <h2 class="mb-2"></h2>
    @php
    $is_minor_to_5 = count($items) < 5;
    @endphp
    <!-- Mostrar items de la tienda -->
    <div class="row {{ $is_minor_to_5 ? 'gap-5' : '' }}">
        @foreach($items as $item)
            @php
                $precioConDescuento = $item->sale_unit_price - $item->descuento_store;
            @endphp
            <div class="col-lg-2 col-md-4 col-sm-4 col-6 d-flex align-items-stretch mb-4">
                <div class="card product-card position-relative">
                    @if($item->descuento_store > 0)
                        <span class="badge bg-danger position-absolute top-0 start-0 m-2">Oferta</span>
                    @endif
                    <a href="{{ route('producto.detalles', $item->id) }}" class="stretched-link">
                        <img src="{{ $item->image_url }}" class="card-img-top" alt="Sin imagen">
                    </a>
                    <div class="card-body d-flex flex-column text-center">
                        <p class="card-text">{{ $item->description }}</p>
                        <div class="card-footer mt-auto">
                        @if($item->descuento_store > 0)
                            <p class="card-text text-muted text-decoration-line-through">
                                Precio: {{ $item->currency_type->symbol }} {{ number_format($item->sale_unit_price, 2) }}
                            </p>
                            <p class="card-text discount-price">

                                <strong>Precio con descuento:</strong> {{ $item->currency_type->symbol }} {{ number_format($precioConDescuento, 2) }}
                            </p>
                        @else
                            <p class="card-text">
                                <strong>Precio:</strong> {{ $item->currency_type->symbol }} {{ number_format($item->sale_unit_price, 2) }}
                            </p>
                        @endif
                            <p class="card-text"><strong>Stock:</strong> {{ intval($item->stock) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        @if($items->isEmpty())
            <div class="col-12 mt-5 text-center">
                <div class="alert alert-warning py-4 shadow-sm rounded-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                    @if(request()->has('search'))
                        <h4 class="alert-heading mb-3">No se encontraron resultados para "{{ request('search') }}"</h4>
                        <p class="mb-0">Intenta con otros términos de búsqueda o navega por nuestras categorías disponibles.</p>
                    @else
                        <h4 class="alert-heading mb-3">¡Oops! No hay productos disponibles</h4>
                        <p class="mb-0">En este momento no se encontraron productos en la tienda. Por favor, vuelve a visitarnos pronto.</p>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>



    </div>

    <!-- Footer -->
    @include('comercio.footer')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
