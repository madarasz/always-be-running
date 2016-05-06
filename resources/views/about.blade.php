@extends('layout.general')

@section('content')
    <h1>This is about</h1>

    @foreach ($tournament_types as $ttype)
        <li>{{ $ttype->type_name }}</li>
    @endforeach
@stop

