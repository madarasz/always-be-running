@extends('layout.general')

@section('content')
    <h3 class="page-header">Edit tournament</h3>
    @include('errors.list')
    {!! Form::open(['method' => 'PATCH', 'url' => "/tournaments/$id"]) !!}
    @include('tournaments.partials.form', ['submitButton' => 'Save tournament'])
    {!! Form::close() !!}
@stop