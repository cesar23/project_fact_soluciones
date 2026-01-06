@extends('tenant.layouts.app')

@section('content')

    <tenant-documents-paid-index
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-documents-paid-index>

@endsection
