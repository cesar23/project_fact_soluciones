@extends('tenant.layouts.app')

@section('content')
    <tenant-dispatches-index
        :type-user="{{ json_encode(auth()->user()->type) }}"
        :configuration="{{$configuration}}"
        :is-auditor="{{ json_encode($isAuditor) }}"
        :document_state_types="{{$document_state_types}}"
        :type="{{json_encode($type)}}"
        :user="{{json_encode(auth()->user())}}"
    ></tenant-dispatches-index>
@endsection
