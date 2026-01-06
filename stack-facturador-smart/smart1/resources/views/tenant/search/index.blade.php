@extends('tenant.layouts.web')

@section('content')

    <tenant-search-index
    :document="{{ isset($document) ? json_encode($document) : '{}' }}"
    ></tenant-search-index>

@endsection