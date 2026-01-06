@extends('tenant.layouts.app')

@section('content')
            <tenant-account-sub-diary-automatic
            :system_sub_diaries='@json($system_sub_diaries)'
            ></tenant-account-sub-diary-automatic>
    
@endsection

