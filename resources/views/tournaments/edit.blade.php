@extends('layout.general')

@section('content')
    <h4 class="page-header">Edit tournament</h4>
    @if ($tournament->incomplete)
        <div class="alert alert-danger view-indicator" id="viewing-as-admin">
            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
            Please fill out the required fields to complete your import.
        </div>
    @endif
    @include('errors.list')
    {!! Form::open(['method' => 'PATCH', 'url' => "/tournaments/$id", 'id' => 'tournament-form']) !!}
    @include('tournaments.partials.form', ['submitButton' => 'Save tournament'])
    {!! Form::close() !!}
@stop