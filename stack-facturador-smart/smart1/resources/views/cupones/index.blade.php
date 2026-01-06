@extends('tenant.layouts.app')

@section('content')
<div class="container mt-4">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="page-header pr-0 d-flex justify-content-between align-items-center mb-3">
        <h2 class="d-flex align-items-center">
            <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
            <span class="ml-2">Cupones</span>
        </h2>
        <div class="right-wrapper pull-right">
            <button class="btn btn-custom btn-sm mt-2 mr-2" data-toggle="modal" data-target="#addCouponModal">
                <i class="bi bi-plus-circle"></i> Nuevo
            </button>
        </div>
    </div>

    <table class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Título</th>
                <th>Descripción</th>
                <th>Imagen</th>
                <th>Descuento</th>
                <th>Fecha de Creación</th>
                <th>Fecha de Actualización</th>
                <th>Fecha de Caducidad</th>
                <th>Código de Barras</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($coupons as $coupon)
                <tr>
                    <td>{{ $coupon->id}}</td>
                    <td>{{ $coupon->nombre }}</td>
                    <td>{{ $coupon->titulo }}</td>
                    <td>{{ $coupon->descripcion }}</td>
                    <td>
                        @if ($coupon->imagen)
                            <img src="{{ asset('storage/' . $coupon->imagen) }}" alt="{{ $coupon->titulo }}" class="img-thumbnail" width="50">
                        @endif
                    </td>
                    <td>{{ $coupon->descuento }}</td>
                    <td>{{ $coupon->created_at }}</td>
                    <td>{{ $coupon->updated_at }}</td>
                    <td>{{ $coupon->fecha_caducidad }}</td>
                    <td>
                        @if ($coupon->barcode)
                            <img src="{{ $coupon->barcode }}" alt="Código de Barras" class="barcode-img">
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('tenant.coupons.show', $coupon->id) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('tenant.coupons.edit', $coupon->id) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('tenant.coupons.destroy', $coupon->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="addCouponModal" tabindex="-1" role="dialog" aria-labelledby="addCouponModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addCouponModalLabel">Agregar Cupón</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="addCouponForm" action="{{ route('tenant.coupons.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="titulo">Título</label>
                <input type="text" class="form-control" id="titulo" name="titulo" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" required></textarea>
            </div>
            <div class="form-group">
                <label for="imagen">Imagen</label>
                <input type="file" class="form-control-file" id="imagen" name="imagen">
            </div>
            <div class="form-group">
                <label for="descuento">Descuento</label>
                <input type="number" class="form-control" id="descuento" name="descuento" required>
            </div>
            <div class="form-group">
                <label for="fecha_caducidad">Fecha de Caducidad</label>
                <input type="date" class="form-control" id="fecha_caducidad" name="fecha_caducidad" required>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('addCouponForm').submit();">Guardar</button>
      </div>
    </div>
  </div>
</div>

@endsection

<style>
.barcode-img {
    max-width: 150px;
    height: auto;
    width: auto;
    object-fit: contain;
}
</style>
