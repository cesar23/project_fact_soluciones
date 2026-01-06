@extends('tenant.layouts.app')

@section('content')

    <tenant-sire-appendix
    :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}"
   ></tenant-sire-appendix>

@endsection