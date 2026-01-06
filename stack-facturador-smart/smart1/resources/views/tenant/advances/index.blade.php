@extends('tenant.layouts.app')

@section('content')

    <advances-index 
    :type="{{json_encode(request('type'))}}"
    :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    :type-user="{{json_encode(Auth::user()->type)}}"  ></advances-index>

@endsection
