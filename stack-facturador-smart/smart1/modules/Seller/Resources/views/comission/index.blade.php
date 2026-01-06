@extends('tenant.layouts.app')

@section('content')
    <tenant-comission-index
    :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-comission-index>
@endsection