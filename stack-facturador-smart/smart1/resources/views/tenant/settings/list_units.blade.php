@extends('tenant.layouts.app')

@section('content')
    <tenant-unit_types-index :pdf="{{ json_encode($pdf ?? false) }}" :type-user="{{ json_encode(Auth::user()->type) }}">
    </tenant-unit_types-index>
@endsection
