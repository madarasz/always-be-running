@extends('layout.general')

@section('content')
    <h4 class="page-header">Create new tournament</h4>
    @include('errors.list')
    {!! Form::open(['url' => '/tournaments', 'id' => 'tournament-form']) !!}
        {!! Form::hidden('temp_id', $temp_id, ['id' => 'temp_id']) !!} {{-- temp id for unofficial prize connection --}}
        @include('tournaments.partials.form', ['submitButton' => 'Create tournament'])
    {!! Form::close() !!}
@stop

