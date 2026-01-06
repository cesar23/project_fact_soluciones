{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Document</title>
</head>

<body>
    testando ando
</body>
<script src="{{ asset('js/app.js') }}"></script>

</html> --}}
@extends('tenant.layouts.restaurant')


@section('content')
    <tenant-restaurant-index :configuration="{{ $configuration }}"></tenant-restaurant-index>
@endsection
