@extends('tenant.layouts.app')

@section('content')

    <tenant-categories-index
        :configuration="{{\App\Models\Tenant\Configuration::getConfig()}}"
    ></tenant-categories-index>

@endsection