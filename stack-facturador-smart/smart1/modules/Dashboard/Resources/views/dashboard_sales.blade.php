@extends('tenant.layouts.app')

@section('content')
    <tenant-dashboard-sales
        :type-user="{{json_encode(Auth::user()->type)}}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-dashboard-sales>
@endsection
