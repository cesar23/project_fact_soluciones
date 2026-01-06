@extends('tenant.layouts.app')

@section('content')
    @if (isset($record))
        <survey-answer-index :record="{{ $record }}"></survey-answer-index>
    @else
        <survey-answer-index></survey-answer-index>
    @endif
@endsection
