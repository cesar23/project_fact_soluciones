@extends('tenant.layouts.app')

@section('content')
    <tenant-certificate-create-template
        :certificate="{{ json_encode($certificate) }}"
    ></tenant-certificate-create-template>
@endsection
