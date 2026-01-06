@extends('tenant.layouts.app')

@section('content')
    <tenant-person-packers-index
        :type-user="{{ json_encode(auth()->user()->type) }}"
        :configuration="{{$configuration}}"
    ></tenant-person-packers-index>
@endsection
