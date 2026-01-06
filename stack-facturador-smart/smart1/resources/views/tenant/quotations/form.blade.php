@extends('tenant.layouts.app')

@section('content')
    <tenant-quotations-form :type-user="{{ json_encode(Auth::user()->type) }}"
        :user="{{ json_encode(Auth::user()->getDataOnlyAuthUser()) }}"
        :sale-opportunity-id="{{ json_encode($saleOpportunityId) }}"
        :quotations_optional ="{{ json_encode($quotations_optional) }}"
        :quotations_optional_value ="{{ json_encode($quotations_optional_value) }}"
        :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}"></tenant-quotations-form>
@endsection
