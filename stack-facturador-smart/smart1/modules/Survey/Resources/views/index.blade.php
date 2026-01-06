@extends('tenant.layouts.app')

@section('content')
    <survey-index :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}"
        :type-user="{{ json_encode(Auth::user()->type) }}"></survey-index>
@endsection
