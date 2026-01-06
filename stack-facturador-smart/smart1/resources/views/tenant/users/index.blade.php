@extends('tenant.layouts.app')

@section('content')
    @php
        $user = auth()->user();
    @endphp
    <tenant-users-index 
        :is-integrate-system="{{ json_encode(\Modules\BusinessTurn\Models\BusinessTurn::isIntegrateSystem()) }}"
        :type-user="{{ json_encode($user->type) }}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
        :virtual-store="false"
        :current-user-id="{{ json_encode($user->id)}}"
        ></tenant-users-index>

@endsection