@extends('tenant.layouts.app')

@section('content')

    <tenant-purchase-orders-form 
    
    :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}"
    :id="{{ json_encode($id) }}" :sale-opportunity="{{ json_encode($sale_opportunity) }}"></tenant-purchase-orders-form>

@endsection