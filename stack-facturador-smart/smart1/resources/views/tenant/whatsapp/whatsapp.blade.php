@extends('tenant.layouts.app')
@section('content')
    <whatsapp-index
    :sender="{{json_encode($sender)}}"
    :name="{{json_encode($name)}}"
    :api_whatsapp="{{json_encode($api_whatsapp)}}">
</whatsapp-index>
@endsection