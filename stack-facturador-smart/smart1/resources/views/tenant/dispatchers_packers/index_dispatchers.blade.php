@extends('tenant.layouts.app')

@section('content')
    <tenant-person-dispatchers-index
        :type-user="{{ json_encode(auth()->user()->type) }}"
        :configuration="{{$configuration}}"
    ></tenant-person-dispatchers-index>
@endsection
