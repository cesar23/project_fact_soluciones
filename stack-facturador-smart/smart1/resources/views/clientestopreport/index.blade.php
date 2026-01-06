@extends('tenant.layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <tenant-customer-top-report
                :company="{{ \App\Models\Tenant\Company::active() }}"
                :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}"
                :type-user="{{ json_encode(auth()->user()->type) }}">
            </tenant-customer-top-report>
        </div>
    </div>
@endsection
