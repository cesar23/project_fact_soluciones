@extends('tenant.layouts.app')
@section('content')
    <system-whatsapp-questions
    :sender="{{json_encode($sender)}}"
    :api_whatsapp="{{json_encode($api_whatsapp)}}"
    >
</system-whatsapp-questions>
@endsection