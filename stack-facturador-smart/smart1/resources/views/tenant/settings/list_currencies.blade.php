@extends('tenant.layouts.app')

@section('content')
    <tenant-currency-types-index 
    
    :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    :type-user="{{json_encode(Auth::user()->type)}}"></tenant-currency-types-index>
@endsection
