{{-- resources/views/comanda/create_personal.blade.php --}}

@extends('tenant.layouts.app')

@section('title', 'Crear Personal')

@section('content')
    <div class="container">
        <h1>Crear Personal</h1>

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

        <form action="{{ route('tenant.personal.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
            </div>
            <div class="form-group">
                <label for="idrol">Rol</label>
                <select class="form-control" id="idrol" name="idrol" required>
                    <option value="">Seleccione un rol</option>
                    @foreach($roles as $rol)
                        <option value="{{ $rol->id }}" {{ old('idrol') == $rol->id ? 'selected' : '' }}>{{ $rol->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="genero">Género</label>
                <input type="text" class="form-control" id="genero" name="genero" value="{{ old('genero') }}" required>
            </div>
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input type="text" class="form-control" id="usuario" name="usuario" value="{{ old('usuario') }}" required>
            </div>
            <div class="form-group">
                <label for="contraseña">Contraseña</label>
                <input type="password" class="form-control" id="contraseña" name="contraseña" required>
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </form>

        {{-- Tabla de usuarios existentes --}}
        <h2>Usuarios Existentes</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Rol</th>
                    <th>Género</th>
                    <th>Usuario</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($personales as $personal)
                    <tr>
                        <td>{{ $personal->id }}</td>
                        <td>{{ $personal->nombre }}</td>
                        <td>{{ $personal->rol->nombre }}</td>
                        <td>{{ $personal->genero }}</td>
                        <td>{{ $personal->usuario }}</td>
                        <td>
                            <form action="{{ route('tenant.personal.destroy', $personal->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">
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
