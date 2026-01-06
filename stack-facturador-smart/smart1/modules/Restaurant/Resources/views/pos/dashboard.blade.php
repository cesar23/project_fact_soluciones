@extends('tenant.layouts.pos')
@section('content')
     <tenant-restaurant-pos 
          :company="{{$company}}" 
          :date_opencash="{{json_encode($date_opencash)}}"
          :desarrollador="{{json_encode($desarrollador)}}" 
          :configuration="{{ $configuration}}" 
          :auth_login="{{ $auth_login}}" 
          :establishments="{{ $establishments}}"></tenant-restaurant-pos>
@endsection
