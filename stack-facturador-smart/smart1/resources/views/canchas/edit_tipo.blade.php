@extends('tenant.layouts.app')

@section('title', 'Editar Tipo de Reserva')

@section('content')
<div class="container">
    <h1 class="mt-4">Editar reserva</h1>

    <form method="POST" action="{{ route('tenant.canchas_tipo.update', $canchaTipo->id) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="{{ $canchaTipo->nombre }}" required>
        </div>
        <div class="mb-3">
            <label for="ubicacion" class="form-label">Ubicación</label>
            <input type="text" class="form-control" id="ubicacion" name="ubicacion" value="{{ $canchaTipo->ubicacion }}" required>
        </div>
        <div class="mb-3">
            <label for="capacidad" class="form-label">Deescripción</label>
            <input type="number" class="form-control" id="capacidad" name="capacidad" value="{{ $canchaTipo->capacidad }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
</div>
@endsection
