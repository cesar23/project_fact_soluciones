@extends('tenant.layouts.worker')
@section('content')
    <restaurant-worker-dashboard :configuration="{{ $configuration }}" :areas="{{ $areas }}"
        :company="{{ $company }}"
        :tables_area="{{ $tables_area }}"
        :foods="{{ $foods }}" 
        :categories="{{ $categories }}" 
        :status_table="{{ $status_table }}"
        :user="{{ json_encode(Auth::user()) }}">
    </restaurant-worker-dashboard>
@endsection
