@extends('tenant.layouts.app')

@section('content')
    <tenant-documents-index 
        :mode-dev="{{ json_encode(config('configuration.app_dev')) }}"
        :is-optometry="{{ json_encode($is_optometry) }}"
        :to_anulate="{{ json_encode($to_anulate) }}" :is-client="{{ json_encode($is_client) }}"
        :is-auditor="{{ json_encode($is_auditor) }}"
        :is-comercial="{{ json_encode($is_comercial) }}"
        :type-user="{{ json_encode(auth()->user()->type) }}" :import_documents="{{ json_encode($import_documents) }}"
        user-id="{{ auth()->user()->id }}"
        :user-permission-edit-cpe="{{ json_encode(auth()->user()->permission_edit_cpe) }}"
        :import_documents_second="{{ json_encode($import_documents_second) }}"
        :document_import_excel="{{ json_encode($document_import_excel) }}" 
        :document_state_types="{{ json_encode($document_state_types) }}" 
        :configuration="{{ $configuration }}"
        :user="{{ json_encode(auth()->user()) }}"
        :view_apiperudev_validator_cpe="{{ json_encode($view_apiperudev_validator_cpe) }}"
        :view_validator_cpe="{{ json_encode($view_validator_cpe) }}"></tenant-documents-index>
@endsection

@push('scripts')
    <script type="text/javascript">
        $(function() {
            'use strict';
            $(".tableScrollTop,.tableWide-wrapper").scroll(function() {
                $(".tableWide-wrapper,.tableScrollTop")
                    .scrollLeft($(this).scrollLeft());
            });
        });
    </script>
@endpush
