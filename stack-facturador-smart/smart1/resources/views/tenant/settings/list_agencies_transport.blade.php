@extends('tenant.layouts.app')

@section('content')
    <tenant-agencies-transport :type-user="{{json_encode(Auth::user()->type)}}" ></tenant-agencies-transport>
@endsection
