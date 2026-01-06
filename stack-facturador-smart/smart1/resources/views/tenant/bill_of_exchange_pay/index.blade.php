@extends('tenant.layouts.app')

@section('content')
    <bill-of-exchange-pay-index :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}"
        :type-user="{{ json_encode(Auth::user()->type) }}"></bill-of-exchange-pay-index>
@endsection
