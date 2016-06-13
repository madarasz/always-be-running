@extends('layout.general')

@section('content')
    <h4 class="page-header">Edit tournament</h4>
    @include('errors.list')
    {!! Form::open(['method' => 'PATCH', 'url' => "/tournaments/$id"]) !!}
    @include('tournaments.partials.form', ['submitButton' => 'Save tournament'])
    {!! Form::close() !!}
@stop