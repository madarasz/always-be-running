@extends('layout.general')

@section('content')
    <h4 class="page-header">Create new tournament</h4>
    @include('errors.list')
    {!! Form::open(['url' => '/tournaments', 'id' => 'tournament-form']) !!}
        @include('tournaments.partials.form', ['submitButton' => 'Create tournament'])
    {!! Form::close() !!}
@stop

