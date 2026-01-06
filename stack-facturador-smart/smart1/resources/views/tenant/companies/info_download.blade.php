@extends('tenant.layouts.app')

@section('content')
    <tenant-companies-download-all-info :type-user="{{ json_encode(Auth::user()->type) }}"
        :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}"></tenant-companies-download-all-info>
@endsection
