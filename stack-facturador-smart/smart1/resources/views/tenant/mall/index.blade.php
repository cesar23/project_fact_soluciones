@extends('tenant.layouts.app')

@section('content')
    <tenant-mall-index :company="{{ json_encode($company) }}"
        :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}"></tenant-mall-index>
@endsection
