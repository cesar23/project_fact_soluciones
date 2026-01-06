@extends('tenant.layouts.app')

@section('content')
    <tenant-state-technical-services-index :type-user="{{json_encode(Auth::user()->type)}}" ></tenant-state-technical-services-index>
@endsection
