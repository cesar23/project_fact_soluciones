@extends('tenant.layouts.app')

@section('content')

    <tenant-finance-global-payments-index
    :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-finance-global-payments-index>

@endsection