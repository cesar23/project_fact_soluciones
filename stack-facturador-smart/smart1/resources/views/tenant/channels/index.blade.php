@extends('tenant.layouts.app')

@section('content')
    <tenant-channels-index
        :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}"
        :type-user="{{ json_encode(Auth::user()->type) }}"></tenant-channels-index>
@endsection
