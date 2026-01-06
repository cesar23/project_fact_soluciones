@extends('tenant.layouts.app')

@section('content')
    <tenant-suscription-name-index :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}">
    </tenant-suscription-name-index>
@endsection
