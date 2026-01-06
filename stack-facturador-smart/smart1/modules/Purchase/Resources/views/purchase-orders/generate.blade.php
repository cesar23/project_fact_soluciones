@extends('tenant.layouts.app')

@section('content')

    <tenant-purchase-orders-generate 
    :is-quotation="{{ json_encode($isQuotation) }}"
    :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}"
    :purchase_quotation="{{ json_encode($purchase_quotation) }}"></tenant-purchase-orders-generate>

@endsection