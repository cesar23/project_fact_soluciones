@extends('tenant.layouts.app')

@section('content')
    <tenant-items-ecommerce-index :has-woocommerce="{{ json_encode($has_woocommerce) }}"></tenant-items-ecommerce-index>
@endsection
