@extends('tenant.layouts.app')


@section('content')
    <order-concrete 
        :order-concrete-id="{{ json_encode($order_concrete_id) }}"
        :document-type-id="{{ json_encode($document_type_id) }}" 
        :document-id="{{ json_encode($document_id) }}"
    ></order-concrete>
@endsection 