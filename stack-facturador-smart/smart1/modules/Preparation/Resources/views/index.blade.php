@extends('tenant.layouts.app')

@section('content')
    <tenant-preparation-index
    type="{{ '' }}"
    :configuration="{{\App\Models\Tenant\Configuration::first()->toJson()}}"
    :type-user="{{json_encode(Auth::user()->type)}}"
    ></tenant-preparation-index>
@endsection
