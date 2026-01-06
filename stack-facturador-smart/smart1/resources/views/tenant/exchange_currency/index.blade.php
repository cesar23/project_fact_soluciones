@extends('tenant.layouts.app')

@section('content')
    @php
        $user = auth()->user();
    @endphp
    <tenant-exchange-currency-index
        :configuration="{{\App\Models\Tenant\Configuration::getConfig()}}"
    ></tenant-exchange-currency-index>

@endsection