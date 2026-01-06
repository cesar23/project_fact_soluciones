@extends('tenant.layouts.app')

@section('content')

    <inventory-index :type-user="{{ json_encode(auth()->user()->type) }}"
        :can-ajust-inventory="{{ json_encode($canAjustInventory) }}"
        ></inventory-index>

@endsection
