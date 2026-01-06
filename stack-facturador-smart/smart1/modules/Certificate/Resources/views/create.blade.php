@extends('tenant.layouts.app')

@section('content')
    <tenant-certificate-create
        :certificate="{{ json_encode($certificate) }}"
        :templates="{{ json_encode($templates) }}"
    ></tenant-certificate-create>
@endsection
