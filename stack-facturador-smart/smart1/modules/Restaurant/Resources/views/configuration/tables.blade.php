@extends('tenant.layouts.app')


@section('content')
    <tenant-restaurant-tables
        :configurations="{{ json_encode($configurations) }}"
    ></tenant-restaurant-tables>
@endsection
