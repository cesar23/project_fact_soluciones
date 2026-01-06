
@extends('tenant.layouts.app')

@section('title', 'Listado de Comandas')

@section('content')
    <div class="container">
        <h1>Listado de Comandas</h1>
        <div class="mb-3">
            <a href="{{ route('tenant.personal.create') }}" class="btn btn-primary">Crear Personal</a>
            <a href="{{ route('tenant.roles.create') }}" class="btn btn-primary">Crear Rol</a>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Código</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                    <tr>
                        <td>{{ $record->id }}</td>
                        <td>{{ $record->nombre }}</td>
                        <td>{{ $record->codigo }}</td>
                        <td>
                            <!-- Agregar acciones como editar o eliminar aquí -->
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
