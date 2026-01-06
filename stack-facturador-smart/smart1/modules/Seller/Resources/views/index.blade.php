@extends('tenant.layouts.app')

@section('content')
    <tenant-seller-index
    :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-seller-index>
@endsection