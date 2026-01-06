@extends('tenant.layouts.app')

@section('content')
        <tenant-preparation-order-transformation-create
        :record-id="{{ json_encode($id) }}"
        ></tenant-preparation-order-transformation-create>
@endsection
