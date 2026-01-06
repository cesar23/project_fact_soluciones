@extends('tenant.layouts.app')

@section('content')
    <tenant-yape-plin-qr :type-user="{{ json_encode(Auth::user()->type) }}"></tenant-yape-plin-qr>
@endsection
