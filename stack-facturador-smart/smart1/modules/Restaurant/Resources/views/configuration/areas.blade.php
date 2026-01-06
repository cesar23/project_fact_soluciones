@extends('tenant.layouts.app')
@section('content')
    <tenant-restaurant-areas :configurations="{{ json_encode($configurations) }}">
    </tenant-restaurant-areas>
@endsection
