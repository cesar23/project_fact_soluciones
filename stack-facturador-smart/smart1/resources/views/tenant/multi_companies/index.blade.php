@extends('tenant.layouts.app')

@section('content')
    <tenant-multi-companies-index 

        :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}">
    </tenant-multi-companies-index>
@endsection
