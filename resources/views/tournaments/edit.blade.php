@extends('layout.general')

@section('content')
    <h4 class="page-header">Edit tournament</h4>
    @include('errors.list')
    {!! Form::open(['method' => 'PATCH', 'url' => "/tournaments/$id", 'files' => true, 'id' => 'tournament-form']) !!}
    @include('tournaments.partials.form', ['submitButton' => 'Save tournament'])
    {!! Form::close() !!}
@stop