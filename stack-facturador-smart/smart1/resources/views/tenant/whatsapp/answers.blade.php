@extends('tenant.layouts.app')
@section('content')
    <whatsapp-answers
    :sender="{{json_encode($sender)}}"
    :api_whatsapp="{{json_encode($api_whatsapp)}}"
    ></whatsapp-answers>
@endsection