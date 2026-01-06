@extends('tenant.layouts.app')

@section('content')
    <survey-respondets-index
        :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}"
        :type-user="{{ json_encode(Auth::user()->type) }}"></survey-respondets-index>
@endsection
