@extends('tenant.layouts.app')


@section('content')
    <tenant-boxes-reports :restaurant={{ true }}  :users="{{json_encode($users)}}"></tenant-boxes-reports>
@endsection
