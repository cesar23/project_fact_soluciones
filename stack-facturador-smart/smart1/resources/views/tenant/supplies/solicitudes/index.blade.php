@extends('tenant.layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <tenant-supplies-solicitudes-lists
                :for-operators="{{ json_encode($forOperators) }}"
            ></tenant-supplies-solicitudes-lists>
        </div>
    </div>
@endsection