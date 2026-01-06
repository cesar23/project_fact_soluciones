@extends('tenant.layouts.app')

@section('content')
<div class="container">
    <h1>Editar Cupón</h1>
    <form action="{{ route('tenant.coupons.update', $coupon->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="{{ $coupon->nombre }}">
        </div>
        <div class="form-group">
            <label for="titulo">Título</label>
            <input type="text" class="form-control" id="titulo" name="titulo" value="{{ $coupon->titulo }}">
        </div>
        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion">{{ $coupon->descripcion }}</textarea>
        </div>
        <div class="form-group">
            <label for="imagen">Imagen</label>
            <input type="file" class="form-control" id="imagen" name="imagen">
            @if ($coupon->imagen)
                <img src="{{ asset('storage/' . $coupon->imagen) }}" alt="{{ $coupon->titulo }}" width="50">
            @endif
        </div>
        <div class="form-group">
            <label for="descuento">Descuento</label>
            <input type="number" class="form-control" id="descuento" name="descuento" value="{{ $coupon->descuento }}">
        </div>
        <div class="form-group">
            <label for="fecha_caducidad">Fecha de Caducidad</label>
            <input type="date" class="form-control" id="fecha_caducidad" name="fecha_caducidad" value="{{ $coupon->fecha_caducidad }}">
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</div>
@endsection
