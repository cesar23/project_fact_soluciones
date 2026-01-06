@extends('tenant.layouts.app')

@section('content')
    @php
        $user = auth()->user();
        $type = $user->type;
        $establishment_id = $user->establishment_id;
    @endphp
    <tenant-dashboard-index
    	:type-user="{{ json_encode($type) }}"
        :establishment-id="{{ json_encode($establishment_id) }}"
    	:soap-company="{{ json_encode($soap_company) }}"
        :configuration="{{ json_encode($configuration) }}">
    </tenant-dashboard-index>

@endsection
