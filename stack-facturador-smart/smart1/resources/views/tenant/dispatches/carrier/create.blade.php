@extends('tenant.layouts.app')

@section('content')
    <tenant-dispatch_carrier-form
        :document="{{ json_encode($document) }}"
        :parent-table="{{ json_encode($parentTable) }}"
        :parent-id="{{ json_encode($parentId) }}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
        :auth-user="{{json_encode(Auth::user()->getDataOnlyAuthUser())}}"
        :establishment-data="{{ json_encode($establishmentData) }}"
    ></tenant-dispatch_carrier-form>
@endsection
