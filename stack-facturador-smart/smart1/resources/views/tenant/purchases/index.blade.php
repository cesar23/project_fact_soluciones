@extends('tenant.layouts.app')

@section('content')

    <tenant-purchases-index
        :validator_cpe="{{ json_encode($validator_cpe) }}"
        :type-user="{{json_encode(Auth::user()->type)}}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-purchases-index>

@endsection
