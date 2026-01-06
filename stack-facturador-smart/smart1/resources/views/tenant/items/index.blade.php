@extends('tenant.layouts.app')

@section('content')
    <tenant-items-index
        :is-food-dealer="{{ json_encode($is_food_dealer) }}"
        :is-comercial="{{ json_encode($is_comercial) }}"
        type="{{ $type ?? '' }}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
        :type-user="{{json_encode(Auth::user()->type)}}"
    ></tenant-items-index>
@endsection
