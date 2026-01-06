@extends('tenant.layouts.app')

@section('content')
    <tenant-payroll-index :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}"
        :type-user="{{ json_encode(Auth::user()->type) }}"></tenant-payroll-index>
@endsection
