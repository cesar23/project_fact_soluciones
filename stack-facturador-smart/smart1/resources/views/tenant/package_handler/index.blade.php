@extends('tenant.layouts.app')

@section('content')

    <tenant-package-handler-index
    :type-user="{{ json_encode(auth()->user()->type) }}"
    :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-package-handler-index>

@endsection