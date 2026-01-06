@extends('tenant.layouts.app')

@section('content')
    <tenant-web-state-deliveries-index :type-user="{{json_encode(Auth::user()->type)}}" ></tenant-web-state-deliveries-index>
@endsection
