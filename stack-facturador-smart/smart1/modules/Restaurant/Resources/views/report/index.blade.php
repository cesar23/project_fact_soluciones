@extends('tenant.layouts.pos')
@section('content')
    <tenant-boxes-reports-pos :restaurant={{ true }} :users="{{ $users }}"></tenant-boxes-reports-pos>
@endsection
