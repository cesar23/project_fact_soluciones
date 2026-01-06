<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('css/comercio.css') }}" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    @include('comercio.navbar')

    <div class="container mt-5">
    <h1>Productos en la categoría: {{ $tag->name }}</h1>

    @if($items->isEmpty())
        <p>No hay productos en esta categoría.</p>
    @else
        @php
            $is_minor_to_5 = count($items) < 5;
        @endphp 
        <div class="row {{ $is_minor_to_5 ? 'gap-5' : '' }}">
            @foreach($items as $item)
                @php
                    $precioConDescuento = $item->sale_unit_price - $item->descuento_store;
                @endphp
                <div class="col-lg-2 col-md-4 col-sm-4 col-6 d-flex align-items-stretch mb-4">

                    <div class="card position-relative">
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
                                        Precio: {{ $item->currency_type->symbol }} {{ $item->sale_unit_price }}
                                    </p>
                                    <p class="card-text discount-price">
                                        <strong>Precio con descuento:</strong> {{ $item->currency_type->symbol }} {{ number_format($precioConDescuento, 2) }}
                                    </p>
                                @else
                                    <p class="card-text">
                                        <strong>Precio:</strong> {{ $item->currency_type->symbol }} {{ $item->sale_unit_price }}
                                    </p>
                                @endif
                                <p class="card-text"><strong>Stock:</strong> {{ $item->stock }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>


    <!-- Footer -->
    @include('comercio.footer')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
