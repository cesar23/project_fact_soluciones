@extends('tenant.layouts.app')

@section('content')

    <tenant-purchase-orders-index
    :configuration="{{\App\Models\Tenant\Configuration::getConfig()}}"
        
    ></tenant-purchase-orders-index>

@endsection