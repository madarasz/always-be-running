@extends('layout.general')

@section('content')
    <h3 class="page-header">{{ $tournament->title }}</h3>
    @include('partials.message')
    <div class="row">
        <div class="col-md-10 col-md-offset-1 col-xs-12">
            <h4>{{ $tournament->location_city }}, {{$tournament->location_country == 840 && $tournament->location_us_state !=52 ? "$state_name, " : ''}}{{ $country_name }}</h4>
        </div>
    </div>
@stop

