@extends('tenant.layouts.app')


@section('content')
    <restaurant-food-list :categories="{{ $categories }}"
                          :configurations="{{ $configurations }}"
                          :foods="{{ $foods }}"></restaurant-food-list>
@endsection
