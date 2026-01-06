@extends('tenant.layouts.app')

@section('content')
    <tenant-documents-note-nv :user="{{ json_encode(auth()->user()) }}"
        :sale_note_affected="{{ json_encode($sale_note_affected) }}"
        :configuration="{{ \App\Models\Tenant\Configuration::getPublicConfig() }}"></tenant-documents-note-nv>
@endsection
