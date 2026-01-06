@extends('tenant.layouts.app')

@section('content')
    <tenant-configurations-shortcuts :type-user="{{ json_encode(Auth::user()->type) }}"
        :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}"
        :company="{{ \App\Models\Tenant\Company::active() }}"
        
        ></tenant-configurations-shortcuts>
@endsection
