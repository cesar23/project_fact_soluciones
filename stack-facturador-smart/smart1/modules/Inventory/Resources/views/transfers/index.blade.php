@extends('tenant.layouts.app')

@section('content')

    <inventory-transfers-index
    :inventory-configuration="{{ json_encode($inventory_configuration)}}"
    ></inventory-transfers-index>

@endsection