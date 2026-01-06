@extends('tenant.layouts.app')

@section('content')

    <tenant-plate-numbers-index :type-user="{{json_encode(Auth::user()->type)}}"  ></tenant-plate-numbers-index>

@endsection