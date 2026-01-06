@extends('tenant.layouts.app')

@section('content')

    <tenant-optometry-services-index
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
        :type-user="{{json_encode(Auth::user()->type)}}"
    ></tenant-optometry-services-index>

@endsection
