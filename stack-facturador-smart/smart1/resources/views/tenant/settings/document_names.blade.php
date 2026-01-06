@extends('tenant.layouts.app')

@section('content')
    <tenant-document-names :type-user="{{ json_encode(Auth::user()->type) }}"></tenant-document-names>
@endsection
