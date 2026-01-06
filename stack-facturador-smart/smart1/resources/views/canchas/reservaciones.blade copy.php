<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Crear Reservación de Canchas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            max-width: 700px;
            width: 100%;
        }
        .form-label {
            font-weight: 400;
            color: #4e4e4e;
        }
        .form-control {
            border-radius: 0.5rem;
            border: 1px solid #ced4da;
            background-color: #fff;
            color: #495057;
            font-size: 1em;
            padding: 10px 15px;
        }
        .btn-primary {
            background-color: #2499e3;
            border-color: #2499e3;
            border-radius: 0.5rem;
            font-size: 1em;
            padding: 10px 20px;
            color: #fff;
        }
        .btn-primary:hover {
            background-color: #2499e3;
            border-color: #2499e3;
        }
        .modal-content {
            border-radius: 12px;
            background-color: #fff;
            color: #000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        #reservation-details {
            padding: 20px;
            border-radius: 8px;
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
        }
        #qr-code-container {
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            
            display: block;
            margin-bottom: 5px;
            font-size: 1em;
        }
        .form-group .form-control {
            height: auto;
        }
    </style>
</head>
<body>
<div class="container">
    <h3 class="text-center mb-4" style="color: #4e4e4e;">Crear nueva reserva</h3>
    <form id="reservationForm">
        @csrf
        <div class="row mb-3">
            <div class="col-md-4 mb-3">
                <label for="tipo_nombre" class="form-label">Nombre de reserva:</label>
                <select class="form-control" id="tipo_nombre" name="nombre" required>
                    <option value=""> </option>
                    @foreach($tiposReservas as $tipo)
                        <option value="{{ $tipo->nombre }}" data-ubicacion="{{ $tipo->ubicacion }}" data-capacidad="{{ $tipo->capacidad }}">{{ $tipo->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="tipo_ubicacion" class="form-label">Ubicación de reserva:</label>
                <select class="form-control" id="tipo_ubicacion" name="ubicacion" required>
                    <option value=""> </option>
                    @foreach($tiposReservas as $tipo)
                        <option value="{{ $tipo->ubicacion }}" data-nombre="{{ $tipo->nombre }}" data-capacidad="{{ $tipo->capacidad }}">{{ $tipo->ubicacion }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="tipo_capacidad" class="form-label">Descripción de reserva:</label>
                <select class="form-control" id="tipo_capacidad" name="capacidad" required>
                    <option value=""></option>
                    @foreach($tiposReservas as $tipo)
                        <option value="{{ $tipo->capacidad }}" data-nombre="{{ $tipo->nombre }}" data-ubicacion="{{ $tipo->ubicacion }}">{{ $tipo->capacidad }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mb-3">

            <div class="col-md-4 form-group">
                <label for="reservante_nombre" class="form-label">Nombre del cliente:</label>
                <input type="text" class="form-control" id="reservante_nombre" name="reservante_nombre" required>
            </div>
            <div class="col-md-4 form-group">
                <label for="reservante_apellidos" class="form-label">Apellidos del cliente:</label>
                <input type="text" class="form-control" id="reservante_apellidos" name="reservante_apellidos" required>
            </div>
            <div class="col-md-4 form-group">
                <label for="numero" class="form-label">Número de celular:</label>
                <input type="text" class="form-control" id="numero" name="numero" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 form-group">
                <label for="fecha_reserva" class="form-label">Fecha de reserva:</label>
                <input type="date" class="form-control" id="fecha_reserva" name="fecha_reserva" required>
            </div>

            <div class="col-md-4 form-group">
                <label for="hora_reserva" class="form-label">Hora de reserva:</label>
                <input type="time" class="form-control" id="hora_reserva" name="hora_reserva" required>
            </div>
            <div class="col-md-4 form-group">
                <label for="tiempo_reserva" class="form-label">Tiempo (en horas):</label>
                <input type="number" class="form-control" id="tiempo_reserva" name="tiempo_reserva" required>
            </div>

        </div>
        <div class="row mb-3">


            <div class="col-md-4 form-group d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Guardar</button>
            </div>
        </div>
    </form>
</div>


<!-- Modal for reservation details -->
<div class="modal fade" id="reservationModal" tabindex="-1" aria-labelledby="reservationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reservationModalLabel">Detalles de la Reserva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="reservation-details">
                <!-- Reservation details will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="printDetails()">Imprimir</button>
            </div>
        </div>
    </div>
</div>
<style>
    .swal2-confirm {
        background-color: #2499e3 !important; /* Cambia el color del fondo del botón */
        border-color: #2499e3 !important;    /* Cambia el color del borde del botón */
        color: white !important;             /* Cambia el color del texto del botón */
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    $('#reservationForm').submit(function (event) {
        event.preventDefault();

        $.ajax({
            url: "{{ route('tenant.canchas.publicStore') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response.success) {
                    var cancha = response.cancha;
                    var detailsHtml = `
                        <p><strong>Nombre:</strong> ${cancha.nombre}</p>
                        <p><strong>Ubicación:</strong> ${cancha.ubicacion}</p>
                        <p><strong>Capacidad:</strong> ${cancha.capacidad}</p>
                        <p><strong>Nombre del Reservante:</strong> ${cancha.reservante_nombre}</p>
                        <p><strong>Apellidos del Reservante:</strong> ${cancha.reservante_apellidos}</p>
                        <p><strong>Celular del Reservante:</strong> ${cancha.numero}</p>
                        <p><strong>Hora de Reserva:</strong> ${cancha.hora_reserva}</p>
                        <p><strong>Fecha de Reserva:</strong> ${cancha.fecha_reserva}</p>
                        <p><strong>Tiempo de Reserva (hrs):</strong> ${cancha.tiempo_reserva}</p>
                        <p><strong>Ticket:</strong> ${cancha.ticket}</p>
                        <div id="qr-code-container" class="text-center">
                            <img src="${cancha.qr_code_path}" alt="Código QR" style="max-width: 200px;">
                        </div>
                    `;
                    $('#reservation-details').html(detailsHtml);
                    var myModal = new bootstrap.Modal(document.getElementById('reservationModal'), {
                        keyboard: false
                    });
                    myModal.show();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hora no disponible',
                        text: response.message || 'Error al crear la reservación',
                        customClass: {
                            confirmButton: 'swal2-confirm'
                        }
                    });
                }
            },
            error: function (xhr, status, error) {
                if (xhr.status === 409) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hora no disponible',
                        text: 'La hora seleccionada ya está reservada. Por favor, elija otra hora.',
                        customClass: {
                            confirmButton: 'swal2-confirm'
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al crear la reservación',
                        customClass: {
                            confirmButton: 'swal2-confirm'
                        }
                    });
                }
            }
        });
    });

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

function printDetails() {
    // Guardar el contenido original del body
    var originalContent = document.body.innerHTML;
    // Obtener los detalles de la reserva y el contenedor del código QR
    var detailsContainer = document.getElementById('reservation-details').innerHTML;

    // Crear el contenido imprimible con los detalles de la reserva
    var printableContent = `
        <div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; padding: 20px; border-radius: 10px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); color: #000;">
            <h2 style="text-align: left; color: #000;">Detalles de la Reserva</h2>
            <div style="margin-bottom: 20px;">
                ${detailsContainer}
            </div>
            <p style="text-align: center; font-size: 14px; color: #000;">Gracias por su reserva. Por favor, presente estos detalles al llegar.</p>
        </div>
    `;

    // Reemplazar el contenido del body con el contenido imprimible
    document.body.innerHTML = printableContent;

    // Imprimir la página
    window.print();

    // Restaurar el contenido original del body
    document.body.innerHTML = originalContent;

    // Recargar la página
    window.location.reload();
}

</script>
</body>
</html>
