@extends('tenant.layouts.app')

@section('content')
 
    <tenant-purchases-form
    :configuration="{{ json_encode($configuration) }}"
    :purchase_order_id="{{ json_encode($purchase_order_id) }}"></tenant-purchases-form>

@endsection