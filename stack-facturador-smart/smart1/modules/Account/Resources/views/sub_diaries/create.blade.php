@extends('tenant.layouts.app')

@section('content')

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <tenant-account-create-sub-diaries :system_sub_diaries="{{ json_encode($system_sub_diaries) }}"></tenant-account-create-sub-diaries>
        </div>
    </div>

@endsection