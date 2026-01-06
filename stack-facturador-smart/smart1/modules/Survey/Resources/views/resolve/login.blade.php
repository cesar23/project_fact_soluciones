@extends('survey::layouts.master')
@section('title', 'Encuesta')
@section('content')
    <survey-resolve-login
    :title="{{json_encode($title)}}"
    :uuid="{{json_encode($uuid)}}"
    :image_url="{{json_encode($image_url)}}"
    ></survey-resolve-login>
@endsection
