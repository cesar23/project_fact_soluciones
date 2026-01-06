@extends('tenant.layouts.app')

@section('content')
<tenant-report-seller-sales-index 
:configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
>
</tenant-report-seller-sales-index>

@endsection