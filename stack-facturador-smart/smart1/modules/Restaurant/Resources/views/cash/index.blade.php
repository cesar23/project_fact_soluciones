@extends('tenant.layouts.pos')
@section('content')
    <tenant-cash-index
         :userid="{{ json_encode($userid) }}">
    </tenant-cash-index>
@endsection
