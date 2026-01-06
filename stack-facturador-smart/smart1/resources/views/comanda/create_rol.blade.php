{{-- resources/views/comanda/create_rol.blade.php --}}

@extends('tenant.layouts.app')

@section('title', 'Crear Rol')

@section('content')
    <div class="container">
        <h1>Crear Rol</h1>

        {{-- Botón para regresar al índice --}}
        <a href="{{ route('tenant.comanda.index') }}" class="btn btn-secondary mb-3">Regresar al índice</a>

        {{-- Mostrar errores de validación --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('tenant.roles.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </form>

        {{-- Tabla de roles existentes --}}
        <h2>Roles Existentes</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $rol)
                    <tr>
                        <td>{{ $rol->id }}</td>
                        <td>{{ $rol->nombre }}</td>
                        <td>
                            <form action="{{ route('tenant.roles.destroy', $rol->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este rol?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
