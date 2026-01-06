@extends('tenant.layouts.app')

@section('content')
    <tenant-charge_discounts-index :type="{{json_encode($type)}}"
        :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}"
        :type-user="{{ json_encode(Auth::user()->type) }}"></tenant-charge_discounts-index>
@endsection
