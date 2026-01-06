@extends('tenant.layouts.app')

@section('content')

    <tenant-account-format
        :companies="{{ json_encode($companies) }}"
        :currencies="{{json_encode($currencies)}}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-account-format>

@endsection
