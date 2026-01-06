@extends('tenant.layouts.app')


@section('content')
    <tenant-restaurant-kitchen :configuration="{{ $configuration }}"></tenant-restaurant-kitchen>
@endsection
