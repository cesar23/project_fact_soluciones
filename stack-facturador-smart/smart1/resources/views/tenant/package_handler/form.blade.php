@extends('tenant.layouts.app')

@section('content')

    <tenant-package-handler-form
        :id="{{ json_encode($id) }}"
        :cashid ="{{ json_encode($cashid) }}"
        :type-user="{{json_encode(Auth::user()->type)}}"
        :auth-user="{{json_encode(Auth::user()->getDataOnlyAuthUser())}}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-package-handler-form>

@endsection
