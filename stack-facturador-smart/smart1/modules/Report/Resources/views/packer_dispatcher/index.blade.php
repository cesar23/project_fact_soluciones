@extends('tenant.layouts.app')

@section('content')



    <tenant-report-packer-dispatcher-sales-index 
            :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
            >
    </tenant-report-packer-dispatcher-sales-index>
@endsection