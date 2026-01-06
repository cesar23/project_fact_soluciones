@extends('tenant.layouts.app')

@section('content')
   <!-- <tenant-report-kardex-index></tenant-report-kardex-index> -->
    <tenant-report-kardex-master
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
        :clothes="{{json_encode(\Modules\BusinessTurn\Models\BusinessTurn::isClothesShoes())}}"
    ></tenant-report-kardex-master>


@endsection

@push('scripts')
    <script></script>
@endpush
