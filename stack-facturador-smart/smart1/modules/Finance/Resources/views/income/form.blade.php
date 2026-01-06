@extends('tenant.layouts.app')

@section('content')

    <tenant-finance-income-form
    :id="{{ json_encode($id) }}"
    ></tenant-finance-income-form>

@endsection