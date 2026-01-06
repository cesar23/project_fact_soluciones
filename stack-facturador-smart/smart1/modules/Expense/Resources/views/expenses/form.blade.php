@extends('tenant.layouts.app')

@section('content')

    <tenant-expenses-form
    :configuration="{{\App\Models\Tenant\Configuration::getConfig()}}"
    :id="{{ json_encode($id) }}"></tenant-expenses-form>

@endsection