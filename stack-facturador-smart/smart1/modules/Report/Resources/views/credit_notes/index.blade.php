@extends('tenant.layouts.app')

@section('content')

    <tenant-report-credit-notes-index 
            :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
            >
    </tenant-report-credit-notes-index>

@endsection