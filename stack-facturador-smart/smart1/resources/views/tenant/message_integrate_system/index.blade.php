@extends('tenant.layouts.app')

@section('content')
    <tenant-message-integrate-system-index 

        :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}">
    </tenant-message-integrate-system-index>
@endsection
