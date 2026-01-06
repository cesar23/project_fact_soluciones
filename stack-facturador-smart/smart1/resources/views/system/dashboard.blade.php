@extends('system.layouts.app')

@section('content')

    <system-clients-index :delete-permission="{{json_encode($delete_permission)}}"
                          :disc-used="{{json_encode($disc_used)}}"
                          :i-used="{{json_encode($i_used)}}"
                          :storage-size="{{json_encode($storage_size)}}"
                          :permissions="{{json_encode($permissions)}}"
                          :version="{{json_encode($version)}}"
                          :is-secondary-admin="{{json_encode(Auth::user()->is_secondary)}}"
                          >
    </system-clients-index>
@endsection
