<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Producto</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.4.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('css/comercio.css') }}" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    @include('comercio.navbar')

    <!-- Detalles del producto -->
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6">
                <div class="product-image position-relative">
                    @if($item->descuento_store > 0)
                        <span class="badge-oferta position-absolute top-0 start-0 m-2">Oferta</span>
                    @endif
                    <img src="{{ $item->image_url }}" class="img-fluid rounded shadow-sm" alt="{{ $item->name }}">
                </div>
                <!-- Miniaturas de la imagen -->
                <div class="mt-3 d-flex justify-content-center">
                    <img src="{{ $item->image_url }}" class="img-thumbnail me-2 rounded" style="width: 60px; height: 60px;" alt="Image Thumbnail">
                    <!-- Agrega más miniaturas si es necesario -->
                </div>
            </div>
            <div class="col-md-6">
                <div class="product-details p-4 rounded shadow-sm bg-white">
                    <!-- <h1 class="display-5 fw-bold">{{ $item->name }}</h1>
                    <h2 class="text-muted">{{ $item->second_name }}</h2> -->
                    <p class="lead">{{ $item->description }}</p>
                    <hr>
                    <div>
                    @if($item->descuento_store > 0)
                        @php
                            $precioConDescuento = $item->sale_unit_price - $item->descuento_store;
                        @endphp
                        <p class="text-muted text-decoration-line-through">
                            Precio: {{ $item->currency_type->symbol }} {{ number_format($item->sale_unit_price, 2) }}
                        </p>
                        <p>
                            <strong>Precio con descuento:</strong> 
                            <span class="text-primary fs-4">{{ $item->currency_type->symbol }} {{ number_format($precioConDescuento, 2) }}</span>
                        </p>
                    @else
                        <p>
                            <strong>Precio:</strong> 
                            <span class="text-primary fs-4"> {{ $item->currency_type->symbol }} {{ number_format($item->sale_unit_price, 2) }}</span>
                        </p>
                    @endif
                    <p>
                        <strong>Stock:</strong> {{ intval($item->stock) }}
                    </p>
                    <p>
                        <strong>Código:</strong> {{ $item->internal_id }}
                    </p>
                        <p><strong>Más detalles:</strong> {{ $item->name }}
                        <br>
                        {{ $item->technical_specifications }}
                        </p>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <strong class="me-3">Cantidad:</strong>
                        <input id="cantidad" type="number" class="form-control form-control-sm w-25" min="1" value="1">
                    </div>

                    <div class="d-flex mb-3">
                        <a id="whatsappButton" href="#" class="btn btn-whatsapp me-2 d-flex align-items-center" target="_blank">
                            <i class="bi bi-whatsapp me-2"></i> Contactar por WhatsApp
                        </a>
                    </div>
                    <!-- <p class="lead">{{ $phone_whatsapp }}</p> -->
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    @include('comercio.footer')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('whatsappButton').addEventListener('click', function(event) {
            event.preventDefault();
            var cantidad = document.getElementById('cantidad').value;
            var descripcion = "{{ $item->description }}";
            var mensaje = "Estoy interesado en este producto: {{ url()->current() }}\nDescripción: " + descripcion + "\nCantidad: " + cantidad;
            var phone_whatsapp = "{{ $phone_whatsapp }}";
            var url = "https://wa.me/" + phone_whatsapp + "?text=" + encodeURIComponent(mensaje);
            window.open(url, '_blank');
        });
    </script>
</body>
</html>
