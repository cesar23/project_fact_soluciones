@extends('tenant.layouts.app')

@section('content')
    <tenant-quotations-edit
        :resource-id="{{json_encode($resourceId)}}"
        :quotations_optional ="{{ json_encode($quotations_optional) }}"
        :quotations_optional_value ="{{ json_encode($quotations_optional_value) }}"
        :type-user="{{json_encode(Auth::user()->type)}}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}">
    </tenant-quotations-edit>
@endsection
