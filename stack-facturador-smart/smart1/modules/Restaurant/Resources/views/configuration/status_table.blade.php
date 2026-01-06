@extends('tenant.layouts.app')


@section('content')
    <tenant-restaurant-items type='status-tables' title='Listado de Estados de Mesas' :configurations="{{ json_encode($configurations) }}"></tenant-restaurant-items>
@endsection
