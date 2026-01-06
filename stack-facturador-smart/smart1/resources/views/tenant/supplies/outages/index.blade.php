@extends('tenant.layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <tenant-supplies-outages-lists :type_outage="{{ json_encode($type_outage) }}"></tenant-supplies-outages-lists>
        </div>
    </div>
@endsection