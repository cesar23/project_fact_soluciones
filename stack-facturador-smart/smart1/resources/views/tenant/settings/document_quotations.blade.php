@extends('tenant.layouts.app')

@section('content')
<tenant-document_quotations :type-user="{{ json_encode(Auth::user()->type) }}">
</tenant-document_quotations>
@endsection
