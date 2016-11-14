@extends('layout.general')

@section('content')
    <h4 class="page-header">Badges</h4>
    <div class="row">
        <div class="col-md-10 col-xs-12 offset-md-1">
            <div class="bracket">
                <p>
                    <strong>These are the currently available badges on the site:</strong>
                </p>
                @foreach($badges as $badge)
                    <div class="row p-b-1">
                        <div class="col-xs-2 text-xs-right">
                            <img src="/img/badges/{{ $badge->filename }}"/>
                        </div>
                        <div class="col-xs-10">
                            <strong>{{ $badge->name }}</strong><br/>
                            {{ $badge->description }}<br/>
                            <div class="small-text">(belonging to {{ $badge->users()->count() }} users)</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@stop

