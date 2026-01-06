@extends('tenant.layouts.app')

@section('content')

    <tenant-reports-hotel-mincetur :configuration="{{json_encode($configuration)}}">

    </tenant-reports-hotel-mincetur>

@endsection
