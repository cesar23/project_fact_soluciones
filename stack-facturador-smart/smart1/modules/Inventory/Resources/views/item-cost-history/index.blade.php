@extends('tenant.layouts.app')

@section('content')

    <item-cost-history-index
        :configuration="{{ json_encode($configuration) }}"
    ></item-cost-history-index>

@endsection
