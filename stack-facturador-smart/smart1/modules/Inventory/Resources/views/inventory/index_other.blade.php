@extends('tenant.layouts.app')

@section('content')

    <inventory-other-index :type-user="{{ json_encode(auth()->user()->type) }}"
        :can-ajust-inventory="{{ json_encode($canAjustInventory) }}"
        ></inventory-other-index>

@endsection
