@extends('tenant.layouts.app')

@section('content')

    <tenant-finance-unpaid-index 
    :customer-id="{{ json_encode($customer_id) }}"
    :type-user="{{ json_encode(auth()->user()->type) }}" :configuration="{{ $configuration }}" ></tenant-finance-unpaid-index>

@endsection
