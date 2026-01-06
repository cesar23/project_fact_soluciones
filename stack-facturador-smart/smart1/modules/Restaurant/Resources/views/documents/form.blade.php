@extends('tenant.layouts.pos')
@push('styles')
    <style type="text/css">
        .v-modal {
            opacity: 0.2 !important;
        }
        .border-custom {
            border-color: rgba(0,136,204, .5) !important;
        }
        @media only screen and (min-width: 768px) {
        	.inner-wrapper {
			    padding-top: 60px !important;
			}
        }

    </style>
@endpush
@section('content')
    <tenant-documents-invoice :id="{{ json_encode($id) }}"
            :configuration="{{json_encode($configuration)}}"
            :is_contingency="{{ json_encode($is_contingency) }}"
            :type-user="{{json_encode(Auth::user()->type)}}"
            :configuration="{{ $configuration }}">
    </tenant-documents-invoice>
@endsection

