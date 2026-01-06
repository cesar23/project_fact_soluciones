@extends('tenant.layouts.app')

@section('content')
    <tenant-dispatch_carrier-index
        :is-auditor="{{ json_encode($isAuditor) }}"
        :document_state_types="{{ json_encode($document_state_types) }}"
        :type-user="{{ json_encode(auth()->user()->type) }}"
        :configuration="{{$configuration}}"
    ></tenant-dispatch_carrier-index>
@endsection
