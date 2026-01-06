@extends('tenant.layouts.app')

@section('content')
    <survey-sections-index  :survey="{{ json_encode($survey) }}"
        :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}"
        :type-user="{{ json_encode(Auth::user()->type) }}"></survey-sections-index>
@endsection
