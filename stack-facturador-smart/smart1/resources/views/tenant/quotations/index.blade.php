@extends('tenant.layouts.app')

@section('content')

    <tenant-quotations-index
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
        :is-commercial="{{ json_encode($isCommercial) }}"
        :is-integrate-system="{{ json_encode(\Modules\BusinessTurn\Models\BusinessTurn::isIntegrateSystem()) }}"
    	:type-user="{{json_encode(Auth::user()->type)}}"
    	:soap-company="{{ json_encode($soap_company) }}"
        :company="{{ json_encode(\App\Models\Tenant\Company::first()) }}"
        :quotations_optional ="{{ json_encode($quotations_optional) }}"
        :quotations_optional_value ="{{ json_encode($quotations_optional_value) }}"
    	:generate-order-note-from-quotation="{{ json_encode($generate_order_note_from_quotation) }}">
    </tenant-quotations-index>

@endsection
