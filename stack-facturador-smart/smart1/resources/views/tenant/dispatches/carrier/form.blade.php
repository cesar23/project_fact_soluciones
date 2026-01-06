@extends('tenant.layouts.app')

@section('content')
    <tenant-dispatch_carrier-form
        :establishment-data="{{ json_encode($establishmentData) }}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
        :auth-user="{{json_encode(Auth::user()->getDataOnlyAuthUser())}}"
    ></tenant-dispatch_carrier-form>
@endsection
