@extends('tenant.layouts.app')

@section('content')
<div class="container">
    <div class="coupon-container">
        @if ($coupon->imagen)
            <div class="image-container">
                <img src="{{ asset('storage/' . $coupon->imagen) }}" alt="{{ $coupon->titulo }}" class="coupon-image">
            </div>
        @endif
        <div class="coupon">
            <div class="coupon-header">
                <h1>{{ $coupon->titulo }}</h1>
            </div>
            <div class="coupon-body">
                <p class="coupon-description">{{ $coupon->descripcion }}</p>
                <p class="coupon-discount">Descuento: <strong>{{ $coupon->descuento }}%</strong></p>
                <p class="coupon-dates">
                    <span>Fecha de Creación: {{ $coupon->created_at->format('d/m/Y') }}</span>
                    <span>Fecha de Actualización: {{ $coupon->updated_at->format('d/m/Y') }}</span>
                    @if ($coupon->fecha_caducidad)
                        <span>Fecha de Caducidad: {{ $coupon->fecha_caducidad->format('d/m/Y') }}</span>
                    @endif
                </p>
            </div>
            <div class="coupon-footer">
                <img src="{{ $coupon->barcode }}" alt="Código de Barras">
            </div>
        </div>
    </div>
    <div class="text-center mt-4">
        <a href="{{ route('tenant.coupons.index') }}" class="btn btn-primary">Regresar</a>
    </div>
</div>
@endsection


<style>
    .coupon-container {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        min-height: 60vh;
        background-color: #f5f5f5;
        padding: 20px;
    }
    .image-container {
        margin-bottom: 20px;
    }
    .coupon-image {
        max-width: 100%;
        height: auto;
    }
    .coupon {
        border: 2px dashed #333;
        padding: 20px;
        max-width: 400px;
        background-color: #fff;
        font-family: Arial, sans-serif;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: opacity 0.5s ease;
        opacity: 1;
    }
    .coupon-header {
        background-color: #f8f8f8;
        padding: 10px;
        text-align: center;
        border-bottom: 2px dashed #333;
    }
    .coupon-body {
        padding: 20px;
        text-align: center;
    }
    .coupon-description {
        font-size: 16px;
        margin-bottom: 15px;
    }
    .coupon-discount {
        font-size: 20px;
        color: #e74c3c;
        margin-bottom: 15px;
    }
    .coupon-dates {
        font-size: 12px;
        color: #888;
        margin-bottom: 20px;
    }
    .coupon-dates span {
        display: block;
    }
    .coupon-footer {
        text-align: center;
        border-top: 2px dashed #333;
        padding: 10px;
    }
    .coupon-footer img {
        max-width: 100%;
        height: auto;
    }
</style>

