{{-- @extends('tenant.layouts.app') --}}
@extends('survey::layouts.master')
@section('title', 'Reservaciones')

@section('content')
<tenant-reservaciones-index
:types="{{ json_encode($types) }}"
></tenant-reservaciones-index>
@endsection
