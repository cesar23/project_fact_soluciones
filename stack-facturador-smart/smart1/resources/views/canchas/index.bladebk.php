@extends('tenant.layouts.app')

@section('title', 'Reservas de Futbol')

@section('content')
<div class="container">
    <h1 class="mt-4">Listado de Reservas</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    <div class="d-flex align-items-center">
        <button class="btn btn-primary me-3" data-bs-toggle="modal" data-bs-target="#addCanchaModal">Agregar Reservas</button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTipoReservasModal">Agregar Tipo De Reservas</button>
                    @error('fecha_reserva')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
    </div>
    <div class="mb-3">
    <form action="{{ route('tenant.canchas.index') }}" method="GET">
        <div class="row">
            <div class="col-md-3">
                <label for="search" class="form-label">Buscar por Nombre o ticket:</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="Buscar..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label for="start_date" class="form-label">Fecha de inicio:</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date', \Carbon\Carbon::now()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <label for="end_date" class="form-label">Fecha de fin:</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date', \Carbon\Carbon::now()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label for="tipo_reserva" class="form-label">Nombre de reserva:</label>
                <select class="form-control" id="tipo_reserva" name="tipo_reserva">
                    <option value="">Seleccione nombre de reserva</option>
                    @foreach($tiposReservas as $tipo)
                        <option value="{{ $tipo->nombre }}" {{ request('tipo_reserva') == $tipo->nombre ? 'selected' : '' }}>{{ $tipo->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Buscar</button>
            </div>
        </div>
    </form>
</div>





<table class="table">
    <thead class="thead-light">
        <tr>
            <th>#</th>
            <th>Nombre del Cliente</th>
            <th>Apellidos del Cliente</th>
            <th>Celular</th>
            <th>Hora de Reserva</th>
            <th>Fecha de Reserva</th>
            <th>Tiempo (hrs)</th>
            <th>Ticket</th>
            <th>Código QR</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody id="cancha-list">
        @foreach($records as $index => $cancha)
            <tr>
                <td class="{{ $cancha->anulado ? 'text-danger' : '' }}">{{ $cancha->id }}</td>
                <td class="{{ $cancha->anulado ? 'text-danger' : '' }}">{{ $cancha->reservante_nombre }}</td>
                <td class="{{ $cancha->anulado ? 'text-danger' : '' }}">{{ $cancha->reservante_apellidos }}</td>
                <td class="{{ $cancha->anulado ? 'text-danger' : '' }}">{{ $cancha->numero }}</td>
                <td class="{{ $cancha->anulado ? 'text-danger' : '' }}">{{ $cancha->hora_reserva }}</td>
                <td class="{{ $cancha->anulado ? 'text-danger' : '' }}">{{ $cancha->fecha_reserva }}</td>
                <td class="{{ $cancha->anulado ? 'text-danger' : '' }}">{{ $cancha->tiempo_reserva }}</td>
                <td class="{{ $cancha->anulado ? 'text-danger' : '' }}">{{ $cancha->ticket }}</td>
                <td class="{{ $cancha->anulado ? 'text-danger' : '' }}"><img src="{{ $cancha->qr_code }}" alt="QR Code" style="width: 60px;"></td>
                <td>
                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#canchaModal" onclick="showCanchaDetails({{ $index }})">Ver</button>
                    @if(!$cancha->anulado)
                        <button class="btn btn-danger" onclick="cancelReservation({{ $cancha->id }})">Anular</button>
                    @else
                        <span class="badge bg-secondary">Anulado</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>



</div>
<style>
        .text-danger {
        color: red !important;
    }
    .table {
        width: 100%;
        margin-bottom: 1rem;
        color: #212529;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.05);
    }

    .table thead th {
        vertical-align: bottom;
        border-bottom: 2px solid #dee2e6;
    }

    .table-bordered {
        border: 1px solid #dee2e6;
    }

    .table-bordered th,
    .table-bordered td {
        border: 1px solid #dee2e6;
    }

    .table-hover tbody tr:hover {
        color: #212529;
        background-color: rgba(0, 0, 0, 0.075);
    }

    .thead-light th {
        background-color: #f8f9fa;
        color: #495057;
    }
</style>
<!-- Modal para ver detalles de la cancha -->
<div class="modal fade" id="canchaModal" tabindex="-1" aria-labelledby="canchaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="canchaModalLabel">Detalles de la Reservas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="cancha-details">
                <p><strong>Nombre:</strong> <span id="cancha-nombre"></span></p>
                <p><strong>Ubicación:</strong> <span id="cancha-ubicacion"></span></p>
                <p><strong>Descripción:</strong> <span id="cancha-capacidad"></span></p>
                <p><strong>Nombre del cliente:</strong> <span id="cancha-reservante-nombre"></span></p>
                <p><strong>Apellidos del cliente:</strong> <span id="cancha-reservante-apellidos"></span></p>
                <p><strong>Numero:</strong> <span id="cancha-numero"></span></p>
                <p><strong>Hora de reserva:</strong> <span id="cancha-hora-reserva"></span></p>
                <p><strong>Fecha de reserva:</strong> <span id="cancha-fecha-reserva"></span></p>
                <p><strong>Tiempo (hrs):</strong> <span id="cancha-tiempo-reserva"></span></p>
                <p><strong>Ticket:</strong> <span id="cancha-ticket"></span></p>
                <div id="qr-code"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="printDetails()">Imprimir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar nuevo tipo de reserva -->
<div class="modal fade" id="addTipoReservasModal" tabindex="-1" aria-labelledby="addTipoReservasModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTipoReservasModalLabel">Crear nueva reserva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addTipoReservasForm" method="POST" action="{{ route('tenant.canchas_tipo.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="ubicacion" class="form-label">Ubicación</label>
                        <input type="text" class="form-control" id="ubicacion" name="ubicacion" required>
                    </div>
                    <div class="mb-3">
                        <label for="capacidad" class="form-label">Descripción</label>
                        <input type="text" class="form-control" id="capacidad" name="capacidad" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
                
                <!-- Formulario de búsqueda para tipos de reservas -->
                <form method="GET" action="{{ route('tenant.canchas_tipo.filter') }}" id="filterForm" class="d-flex mb-4">
                    <input type="text" name="search" class="form-control me-2" placeholder="Buscar..." id="searchInput">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </form>

                <!-- Contenedor para los resultados filtrados dentro del modal -->
                <div id="tiposReservasList">
                    @include('canchas.tipo_reservas_list', ['tiposReservas' => $tiposReservas])
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal para agregar nueva reservq -->
<div class="modal fade" id="addCanchaModal" tabindex="-1" aria-labelledby="addCanchaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCanchaModalLabel">Agregar nueva reserva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addCanchaForm" action="{{ route('tenant.canchas.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="tipo_nombre" class="form-label">Nombre:</label>
                        <select class="form-control" id="tipo_nombre" name="nombre" required>
                            <option value="">Seleccione un nombre</option>
                            @foreach($tiposReservas as $tipo)
                                <option value="{{ $tipo->nombre }}" data-ubicacion="{{ $tipo->ubicacion }}" data-capacidad="{{ $tipo->capacidad }}">{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tipo_ubicacion" class="form-label">Ubicación:</label>
                        <select class="form-control" id="tipo_ubicacion" name="ubicacion" required>
                            <option value="">Seleccione una ubicación</option>
                            @foreach($tiposReservas as $tipo)
                                <option value="{{ $tipo->ubicacion }}" data-nombre="{{ $tipo->nombre }}" data-capacidad="{{ $tipo->capacidad }}">{{ $tipo->ubicacion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tipo_capacidad" class="form-label">Descripción:</label>
                        <select class="form-control" id="tipo_capacidad" name="capacidad" required>
                            <option value="">Seleccione una descripción</option>
                            @foreach($tiposReservas as $tipo)
                                <option value="{{ $tipo->capacidad }}" data-nombre="{{ $tipo->nombre }}" data-ubicacion="{{ $tipo->ubicacion }}">{{ $tipo->capacidad }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="reservante_nombre" class="form-label">Nombre del cliente:</label>
                        <input type="text" class="form-control" id="reservante_nombre" name="reservante_nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="reservante_apellidos" class="form-label">Apellidos del cliente:</label>
                        <input type="text" class="form-control" id="reservante_apellidos" name="reservante_apellidos" required>
                    </div>
                    <div class="mb-3">
                        <label for="numero" class="form-label">Número de celular:</label>
                        <input type="text" class="form-control" id="numero" name="numero" required>
                    </div>
                    <div class="mb-3">
                        <label for="hora_reserva" class="form-label">Hora de reserva:</label>
                        <input type="time" class="form-control" id="hora_reserva" name="hora_reserva" required>
                        @error('fecha_reserva')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="fecha_reserva" class="form-label">Fecha de reserva:</label>
                        <input type="date" class="form-control" id="fecha_reserva" name="fecha_reserva" required>
                    </div>
                    <div class="mb-3">
                        <label for="tiempo_reserva" class="form-label">Tiempo (en horas):</label>
                        <input type="number" class="form-control" id="tiempo_reserva" name="tiempo_reserva" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
    function cancelReservation(id) {
        if (confirm("¿Está seguro de que desea anular esta reserva?")) {
            $.ajax({
                url: '{{ url("canchas/anular") }}/' + id,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        alert('Reserva anulada con éxito.');
                        location.reload();
                    } else {
                        alert('Hubo un problema al anular la reserva.');
                    }
                },
                error: function(xhr, status, error) {
                    alert('Hubo un problema al anular la reserva.');
                }
            });
        }
    }

    function showCanchaDetails(index) {
        var cancha = @json($records)[index];
        document.getElementById('cancha-nombre').innerText = cancha.nombre;
        document.getElementById('cancha-ubicacion').innerText = cancha.ubicacion;
        document.getElementById('cancha-capacidad').innerText = cancha.capacidad;
        document.getElementById('cancha-reservante-nombre').innerText = cancha.reservante_nombre;
        document.getElementById('cancha-reservante-apellidos').innerText = cancha.reservante_apellidos;
        document.getElementById('cancha-numero').innerText = cancha.numero;
        document.getElementById('cancha-hora-reserva').innerText = cancha.hora_reserva;
        document.getElementById('cancha-fecha-reserva').innerText = cancha.fecha_reserva;
        document.getElementById('cancha-tiempo-reserva').innerText = cancha.tiempo_reserva;
        document.getElementById('cancha-ticket').innerText = cancha.ticket;

        document.getElementById('qr-code').innerHTML = '<img src="' + cancha.qr_code + '" alt="QR Code">';
    }

    function printDetails() {
        var originalContent = document.body.innerHTML;
        var detailsContainer = document.getElementById('cancha-details').innerHTML;
        var qrCode = document.getElementById('qr-code').innerHTML;

        var printableContent = `
            <div style="font-family: Arial, sans-serif; background: linear-gradient(to right, #f5f7fa, #c3cfe2); padding: 20px; border-radius: 10px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div style="flex: 1; padding-right: 20px;">
                        <h2 style="text-align: left; color: #333;">Detalle de la reserva</h2>
                        <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Nombre:</strong> ${document.getElementById('cancha-nombre').innerText}</p>
                        <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Ubicación:</strong> ${document.getElementById('cancha-ubicacion').innerText}</p>
                        <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Capacidad:</strong> ${document.getElementById('cancha-capacidad').innerText}</p>
                        <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Nombre del Cliente:</strong> ${document.getElementById('cancha-reservante-nombre').innerText}</p>
                        <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Apellidos del Cliente:</strong> ${document.getElementById('cancha-reservante-apellidos').innerText}</p>
                        <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Celular del Cliente:</strong> ${document.getElementById('cancha-numero').innerText}</p>
                        <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Hora de Reserva:</strong> ${document.getElementById('cancha-hora-reserva').innerText}</p>
                        <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Fecha de Reserva:</strong> ${document.getElementById('cancha-fecha-reserva').innerText}</p>
                        <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Tiempo de Reserva (hrs):</strong> ${document.getElementById('cancha-tiempo-reserva').innerText}</p>
                        <p style="margin: 5px 0; font-size: 16px; color: #333;"><strong>Ticket:</strong> ${document.getElementById('cancha-ticket').innerText}</p>
                    </div>
                    <div style="flex: 0 0 150px; text-align: center;">
                        ${qrCode}
                        <p style="margin-top: 10px; font-size: 16px; color: #333;"><strong>Ticket No:</strong> ${document.getElementById('cancha-ticket').innerText}</p>
                    </div>
                </div>
                <p style="text-align: center; font-size: 14px; color: #666;">Gracias por su reserva. Por favor, presente estos detalles al llegar.</p>
            </div>
        `;

        document.body.innerHTML = printableContent;
        window.print();
        document.body.innerHTML = originalContent;
        window.location.reload();
    }

    $(document).ready(function(){
        $('#tipo_nombre').change(function() {
            var selectedOption = $(this).find('option:selected');
            var ubicacion = selectedOption.data('ubicacion');
            var capacidad = selectedOption.data('capacidad');
            
            $('#ubicacion').val(ubicacion);
            $('#capacidad').val(capacidad);
        });

        $('#filterForm').on('submit', function(e){
            e.preventDefault();
            var search = $('#searchInput').val();
            $.ajax({
                url: "{{ route('tenant.canchas_tipo.filter') }}",
                type: 'GET',
                data: { search: search },
                success: function(response){
                    $('#modalFilteredResults').html(response.html);
                }
            });
        });
    });
    $(document).ready(function(){
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            var searchValue = $('#searchInput').val();
            $.ajax({
                url: '{{ route("tenant.canchas_tipo.filter") }}',
                method: 'GET',
                data: { search: searchValue },
                success: function(response) {
                    $('#tiposReservasList').html(response);
                }
            });
        });
    });

    $(document).ready(function(){
        $('#tipo_nombre').change(function() {
            var selectedOption = $(this).find('option:selected');
            var ubicacion = selectedOption.data('ubicacion');
            var capacidad = selectedOption.data('capacidad');
            
            $('#tipo_ubicacion').val(ubicacion);
            $('#tipo_capacidad').val(capacidad);
        });

        $('#tipo_ubicacion').change(function() {
            var selectedOption = $(this).find('option:selected');
            var nombre = selectedOption.data('nombre');
            var capacidad = selectedOption.data('capacidad');
            
            $('#tipo_nombre').val(nombre);
            $('#tipo_capacidad').val(capacidad);
        });

        $('#tipo_capacidad').change(function() {
            var selectedOption = $(this).find('option:selected');
            var nombre = selectedOption.data('nombre');
            var ubicacion = selectedOption.data('ubicacion');
            
            $('#tipo_nombre').val(nombre);
            $('#tipo_ubicacion').val(ubicacion);
        });
    });
</script>

@endsection
<style>
    .text-danger {
        color: red !important;
    }
</style>