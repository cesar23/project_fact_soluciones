@extends('tenant.layouts.app')

@section('content')
    <tenant-inventory-report
    :user-type="{{ json_encode(auth()->user()->type) }}"
    :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-inventory-report>

@endsection
