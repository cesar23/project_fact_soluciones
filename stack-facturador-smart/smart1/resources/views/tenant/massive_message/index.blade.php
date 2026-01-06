@extends('tenant.layouts.app')

@section('content')
    <tenant-massive-message-index 
        :has-gekawa="{{ json_encode($hasGekawa) }}"
        :user="{{ json_encode(auth()->user()) }}"></tenant-massive-message-index>
@endsection
