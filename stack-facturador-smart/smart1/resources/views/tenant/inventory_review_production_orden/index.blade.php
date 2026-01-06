@extends('tenant.layouts.app')

@section('content')

    <inventory-review-production-orden-index :type-user="{{ json_encode(auth()->user()->type) }}"></inventory-review-production-orden-index>

@endsection
