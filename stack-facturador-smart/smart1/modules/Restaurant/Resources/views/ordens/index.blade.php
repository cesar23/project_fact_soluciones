@extends('tenant.layouts.app')


@section('content')
    <tenant-restaurant-orders :configuration="{{ $configuration }}" :user="{{ json_encode(Auth::user()) }}">
    </tenant-restaurant-orders>
@endsection
