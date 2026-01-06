@extends('tenant.layouts.app')

@section('content')

    <tenant-configurations-woocommerce
    :type-user="{{json_encode(Auth::user()->type)}}"
    :configuration="{{json_encode(\Modules\Ecommerce\Models\WoocommerceConfiguration::first())}}"
    ></tenant-configurations-woocommerce>

@endsection
