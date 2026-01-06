@extends('tenant.layouts.app')

@section('content')
    <tenant-documents-recurrence :user="{{ json_encode(auth()->user()) }}"
        :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}">
    </tenant-documents-recurrence>
@endsection
