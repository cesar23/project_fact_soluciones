@extends('tenant.layouts.app')

@section('content')
    <tenant-dispatches-create
        :series_default="{{json_encode($series_default)}}"
        :document="{{ json_encode($document) }}"
        :request-type="{{ json_encode($type) }}"
        :parent-table="{{ json_encode($parentTable) }}"
        :parent-id="{{ json_encode($parentId) }}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
        :auth-user="{{json_encode(Auth::user()->getDataOnlyAuthUser())}}"
        @if(isset($isAuditor))
            :is-auditor="{{ json_encode($isAuditor) }}"
        @endif
        @if(isset($sale_note))
            :sale_note="{{ json_encode($sale_note) }}"
        @endif

    ></tenant-dispatches-create>
@endsection
