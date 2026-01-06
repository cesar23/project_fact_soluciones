@extends('tenant.layouts.app')

@section('content')
    <tenant-hotel-reservations
        :percentage-igv="{{json_encode($percentageIgv)}}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
        :rooms="{{json_encode($rooms)}}"
    ></tenant-hotel-reservations>
@endsection
