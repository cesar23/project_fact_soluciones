@extends('tenant.layouts.pos')
@section('content')

    <restaurant-documents-index :is-client="{{ json_encode($is_client) }}"
                            :type-user="{{ json_encode(auth()->user()->type) }}"
                            :import_documents="{{ json_encode($import_documents) }}"
                            :configuration="{{json_encode($configuration)}}"
                            :import_documents_second="{{ json_encode($import_documents_second) }}"></restaurant-documents-index>

@endsection
