@extends('tenant.layouts.app')

@section('content')
    <tenant-sire-index
    :company="{{ \App\Models\Tenant\Company::select('number')->first() }}"
    ></tenant-sire-index>
@endsection
