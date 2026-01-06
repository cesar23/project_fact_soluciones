@extends('survey::layouts.master')

@section('content')
    <survey-resolve-index :user_name="{{ json_encode($user_name) }}" :user_email="{{ json_encode($user_email) }}"
        :uuid="{{ json_encode($uuid) }}"
        :has_location="{{ json_encode($has_location) }}"
        :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}"></survey-resolve-index>
@endsection
