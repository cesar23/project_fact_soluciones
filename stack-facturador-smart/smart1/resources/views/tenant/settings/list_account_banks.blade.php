@extends('tenant.layouts.app')

@section('content')
    <tenant-bank_accounts-index 
    :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    :type-user="{{json_encode(Auth::user()->type)}}" ></tenant-bank_accounts-index>
@endsection
