@extends('system.layouts.app')

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('errors'))
    <div class="alert alert-danger">
        {{ session('errors') }}
    </div>
@endif

@if($error)
    <form action="{{ route('errors.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('POST')
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="image_file" class="form-label">Mensaje por falta de pago:</label>
                <textarea id="msg_to_pay" name="msg_to_pay" class="form-control" rows="3">{{ $error->msg_to_pay }}</textarea>            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="image_file" class="form-label">Subir imagen:</label>
                <input type="file" id="image_file" name="image_file" class="form-control">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12 text-center">
                @if($error->img)
                    <img src="{{ asset($error->img) }}" class="img-fluid rounded" alt="Imagen actual">
                @else
                    <p>No hay imagen actual.</p>
                @endif
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="text1" class="form-label">Título:</label>
                <input type="text" id="text1" name="text1" class="form-control" value="{{ $error->titulo }}">
            </div>
            <div class="col-md-4">
                <label for="text2" class="form-label">Comentario:</label>
                <input type="text" id="text2" name="text2" class="form-control" value="{{ $error->comentario2 }}">
            </div>
            <div class="col-md-4">
                <label for="text3" class="form-label">Mensaje:</label>
                <input type="text" id="text3" name="text3" class="form-control" value="{{ $error->adm }}">
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </div>
    </form>
@else
    <div class="row">
        <div class="col-md-12">
            <p>No se encontraron errores.</p>
        </div>
    </div>
@endif

<hr>
<div class="container bg-white p-3 shadow-sm rounded">
    <h3 class="mt-4">Crear Envío</h3>
    <form action="{{ route('envios.store') }}" method="POST" class="mb-4">
        @csrf
        <div class="row mb-3">
            <div class="col-md-2">
                <label for="name" class="form-label">Nombre:</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            <div class="col-md-10">
                <label for="descripcion" class="form-label">Descripción:</label>
                <input type="text" id="descripcion" name="descripcion" class="form-control">
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-primary">Crear Envío</button>
            </div>
        </div>
    </form>

    <div class="mt-4">
        <h5>Instrucciones para la Descripción</h5>
        <p>Puede usar las siguientes variables en la descripción para personalizar los mensajes:</p>
        <ul>
            <li><strong>{nombre}</strong>: El nombre del cliente.</li>
            <li><strong>{tiempo}</strong>: El tiempo asociado al cliente.</li>
            <li><strong>{monto}</strong>: El monto asociado al cliente.</li>
            <li><strong>{fecha}</strong>: La fecha de fin de ciclo de facturación del cliente.</li>
        </ul>
    </div>
</div>


@if ($envios->isNotEmpty())
    <h3 class="mt-5">Lista de Envíos</h3>
    <ul class="list-group mb-4">
        @foreach ($envios as $envio)
            <li class="list-group-item">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <strong>Nombre:</strong> {{ $envio->name }}
                    </div>
                    <form action="{{ route('envios.destroy', $envio->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                </div>
                <div>
                    <strong>Contenido:</strong> {{ $envio->descripcion }}
                </div>
            </li>
        @endforeach
    </ul>
@endif


<hr>

<h3 class="mt-4">Lista de Clientes</h3>
<form method="GET" action="{{ route('system.403.index') }}">
    <div class="row mb-3">
        <div class="col-md-3">
            <label for="start_date" class="form-label">Fecha de Inicio:</label>
            <input type="date" id="start_date" name="start_date" class="form-control" value="{{ request('start_date') }}">
        </div>
        <div class="col-md-3">
            <label for="end_date" class="form-label">Fecha de Fin:</label>
            <input type="date" id="end_date" name="end_date" class="form-control" value="{{ request('end_date') }}">
        </div>
        <div class="col-md-3">
            <label for="color" class="form-label">Color:</label>
            <select id="color" name="color" class="form-select">
                <option value="">Todos</option>
                <option value="red" {{ request('color') == 'red' ? 'selected' : '' }}>Rojo</option>
                <option value="orange" {{ request('color') == 'orange' ? 'selected' : '' }}>Naranja</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="locked" class="form-label">Bloqueado:</label>
            <select id="locked" name="locked" class="form-select">
                <option value="">Todos</option>
                <option value="1" {{ request('locked') == '1' ? 'selected' : '' }}>Sí</option>
                <option value="0" {{ request('locked') == '0' ? 'selected' : '' }}>No</option>
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12 text-end">
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </div>
    </div>
</form>



@if ($clients->isNotEmpty())
    <div class="container bg-white p-3 shadow-sm rounded">
        <div class="mb-3 d-flex align-items-center">
            <select id="bulk-envio-selector" class="form-select me-2" style="max-width: 200px;">
                @foreach ($envios as $envio)
                    <option value="{{ $envio->id }}">{{ $envio->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary" id="bulk-send-message">Enviar Mensaje a Seleccionados</button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered ">
                <thead class="table-dark">
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>Número RUC</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Monto</th>
                        <th>Tiempo</th>
                        <th>Fin del ciclo de facturación</th>
                        <th>Inquilino bloqueado</th>
                        <th>Mensaje</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($clients as $client)
                        <tr>
                            <td><input type="checkbox" class="client-checkbox" name="clients[]" value="{{ $client->id }}"></td>
                            <td>{{ $client->number }}</td>
                            <td>{{ $client->name }}</td>
                            <td>{{ $client->telephone }}</td>
                            <td>{{ $client->monto }}</td>
                            <td>{{ $client->tiempo }}</td>
                            <td class="end-billing-cycle" data-end-date="{{ $client->end_billing_cycle }}">{{ $client->end_billing_cycle }}</td>
                            <td>{{ $client->locked_tenant ? 'Sí' : 'No' }}</td>
                            <td>
                                <select name="envio" class="form-control envio-selector">
                                    @foreach ($envios as $envio)
                                        <option value="{{ $envio->id }}">{{ $envio->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <button class="btn btn-primary btn-sm send-message" data-client-id="{{ $client->id }}">Enviar Mensaje</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <p>No se encontraron clientes.</p>
@endif


@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all');
        const clientCheckboxes = document.querySelectorAll('.client-checkbox');
        const sendButtons = document.querySelectorAll('.send-message');
        const bulkSendButton = document.getElementById('bulk-send-message');
        const bulkEnvioSelector = document.getElementById('bulk-envio-selector');
        const billingCycleCells = document.querySelectorAll('.end-billing-cycle');

        selectAllCheckbox.addEventListener('change', function() {
            clientCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        sendButtons.forEach(button => {
            button.addEventListener('click', function() {
                const clientId = this.getAttribute('data-client-id');
                const envioId = this.closest('tr').querySelector('.envio-selector').value;
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/send-message/${clientId}/${envioId}`;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = '_token';
                hiddenInput.value = csrfToken;

                form.appendChild(hiddenInput);
                document.body.appendChild(form);
                form.submit();
            });
        });

        bulkSendButton.addEventListener('click', function() {
            const selectedClients = Array.from(clientCheckboxes).filter(checkbox => checkbox.checked).map(checkbox => checkbox.value);
            const envioId = bulkEnvioSelector.value;
            if (selectedClients.length > 0) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/send-messages';
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = '_token';
                hiddenInput.value = csrfToken;

                const clientsInput = document.createElement('input');
                clientsInput.type = 'hidden';
                clientsInput.name = 'clients';
                clientsInput.value = JSON.stringify(selectedClients);

                const envioInput = document.createElement('input');
                envioInput.type = 'hidden';
                envioInput.name = 'envio_id';
                envioInput.value = envioId;

                form.appendChild(hiddenInput);
                form.appendChild(clientsInput);
                form.appendChild(envioInput);
                document.body.appendChild(form);

                form.submit();

                alert('Mensajes enviados correctamente.');
            } else {
                alert('Por favor, selecciona al menos un cliente.');
            }
        });

        // Cambiar el color del texto según la fecha
        billingCycleCells.forEach(cell => {
            const endDate = new Date(cell.getAttribute('data-end-date'));
            const today = new Date();
            const diffTime = endDate - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays <= 2) {
                cell.style.color = 'red';
            } else if (diffDays <= 5) {
                cell.style.color = 'orange';
            }
        });
    });
</script>