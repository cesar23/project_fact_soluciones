    @extends('tenant.layouts.app')

    @section('content')
        <div class="row">
            <div class="col-lg-12">
                <tenant-account-sub-diary-create
                    :system_sub_diaries='@json($system_sub_diaries)'
                ></tenant-account-sub-diary-create>
            </div>
        </div>
    @endsection

    @push('scripts')
        <script type="text/javascript" src="{{ asset('modules/Account/Resources/assets/js/views/sub_diaries/create.js') }}"></script>
    @endpush 