@extends('tenant.layouts.app')

@section('content')
    <tenant-dispatches-create
        :is-integrate-system="{{ json_encode(\Modules\BusinessTurn\Models\BusinessTurn::isIntegrateSystem()) }}"
        :series_default="{{ json_encode($series_default) }}"
        :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}"
        :auth-user="{{ json_encode(Auth::user()->getDataOnlyAuthUser()) }}"
        :request-type="{{ json_encode($request_type) }}"></tenant-dispatches-create>
@endsection
