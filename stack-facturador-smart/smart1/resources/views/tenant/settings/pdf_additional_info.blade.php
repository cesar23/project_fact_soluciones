@extends('tenant.layouts.app')

@section('content')
    <tenant-pdf-additional-info :type-user="{{ json_encode(Auth::user()->type) }}"></tenant-pdf-additional-info>
@endsection