@extends('layout.general')

@section('content')
    <h3 class="page-header">Create new tournament</h3>
    @include('errors.list')
    {!! Form::open(['url' => '/tournaments']) !!}
        @include('tournaments.partials.form', ['submitButton' => 'Create tournament'])
    {!! Form::close() !!}
@stop

