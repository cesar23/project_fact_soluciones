@extends('tenant.layouts.app')

@section('content')

    <cash-index 
    :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    :type-user="{{json_encode(Auth::user()->type)}}"  ></cash-index>

@endsection
