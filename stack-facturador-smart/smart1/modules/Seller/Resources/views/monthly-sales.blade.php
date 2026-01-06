@extends('tenant.layouts.app')

@section('content')
    <tenant-seller-monthly-sales-index
    :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-seller-monthly-sales-index>
@endsection