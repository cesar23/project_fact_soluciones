@extends('tenant.layouts.app')

@section('content')

    <tenant-report-summary-sales-index 
            :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
            >
    </tenant-report-summary-sales-index>

@endsection