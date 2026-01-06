@extends('tenant.layouts.app')

@section('content')
    <tenant-payment-method-index 
            :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    :type-user="{{json_encode(Auth::user()->type)}}" ></tenant-payment-method-index>
@endsection
