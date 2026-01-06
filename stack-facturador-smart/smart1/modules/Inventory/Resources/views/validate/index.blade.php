@extends('tenant.layouts.app')

@section('content')

    <inventory-validate-index :type-user="{{ json_encode(auth()->user()->type) }}"></inventory-validate-index>

@endsection
