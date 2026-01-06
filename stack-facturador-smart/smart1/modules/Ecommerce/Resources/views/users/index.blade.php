@extends('tenant.layouts.app')

@section('content')

    <tenant-users-index 
        :is-integrate-system="{{ json_encode(\Modules\BusinessTurn\Models\BusinessTurn::isIntegrateSystem()) }}"
        :type-user="{{ json_encode(auth()->user()->type) }}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
        :virtual-store="true"
        ></tenant-users-index>

@endsection