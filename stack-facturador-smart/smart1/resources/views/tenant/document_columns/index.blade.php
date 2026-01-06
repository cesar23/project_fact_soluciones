@extends('tenant.layouts.app')

@section('content')
    <tenant-document-columns :type-user="{{ json_encode(Auth::user()->type) }}"></tenant-document-columns>
@endsection
