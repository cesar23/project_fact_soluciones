@extends('tenant.layouts.app')

@section('content')

            <tenant-account-ledger-accounts
                :configuration="{{\App\Models\Tenant\Configuration::getConfig()}}"
            ></tenant-account-ledger-accounts>


@endsection
