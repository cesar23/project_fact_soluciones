@extends('tenant.layouts.app')

@section('content')
    <tenant-hotel-reception
        :config='@json($config)'
        :floors='@json($floors)'
        :room-status='@json($roomStatus)'
        :rooms='@json($rooms)'
        :percentage-igv='@json($percentageIgv)'
    ></tenant-hotel-reception>
@endsection
