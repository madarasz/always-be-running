@extends('layout.general')

@section('content')
    <h3 class="page-header">
        @if ($user && ($user->admin || $user->id == $tournament->creator))
            <div class="pull-right">
                {!! Form::open(['method' => 'DELETE', 'url' => "/tournaments/$tournament->id"]) !!}
                    <a href="{{ "/tournaments/$tournament->id/edit" }}" class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</a>
                    {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i> Delete tournament', array('type' => 'submit', 'class' => 'btn btn-danger')) !!}
                {!! Form::close() !!}
            </div>
        @endif
        {{ $tournament->title }}<br/>
        <small>{{ $type }} - <em>created by {{ $tournament->creator }}</em></small>


    </h3>
    @include('partials.message')
    <div class="row">
        <div class="col-md-4 col-xs-12">
            <h4>
                {{ $tournament->location_city }}, {{$tournament->location_country == 840 && $tournament->location_us_state !=52 ? "$state_name, " : ''}}{{ $country_name }}
             - {{ $tournament->date }}<br/>

            </h4>
            @unless($tournament->description === '')
                <p>{!! nl2br(e($tournament->description)) !!}</p>
            @endunless
            @if($tournament->decklist == 1)
                <p><strong><u>decklist is mandatory!</u></strong></p>
            @endif
            <p>
                @unless($tournament->start_time === '')
                    <strong>Starting time</strong>: {{ $tournament->start_time }} (local time)<br/>
                @endunless
                @unless($tournament->location_store === '')
                    <strong>Store/venue</strong>: {{ $tournament->location_store }}<br/>
                @endunless
                @unless($tournament->location_address === '')
                    <strong>Address</strong>: {{ $tournament->location_address }}<br/>
                @endunless
            </p>

        </div>
        <div class="col-md-8 col-xs-12">
            @if ($tournament->concluded)
                <p>
                    <strong>Number of players</strong>: {{ $tournament->players_number }}<br/>
                    @if ($tournament->top_number)
                        <strong>Top cut players:</strong> {{ $tournament->top_number }}<br/>
                    @endif
                    @if ($user)
                        <hr/>
                        <a href="" class="btn btn-success"><i class="fa fa-check-square-o" aria-hidden="true"></i> Claim spot</a>
                        {{--<a href="" class="btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i> Remove claim</a>--}}
                    @endif

                </p>
                <hr/>
                <p>
                    <table class="table table-condensed table-striped">
                        <thead>
                            <th>rank</th>
                            <th>corp</th>
                            <th>runner</th>
                            <th></th>
                        </thead>
                        <tbody>
                            @for ($i = 1; $i <= $tournament->players_number; $i++)
                                <tr>
                                    <td>{{ $i }}.</td>
                                    <td><em>unclaimed</em></td>
                                    <td><em>unclaimed</em></td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </p>
            @endif
            <hr/>
            <p>
                <strong>Registered players</strong>
                @if (count($entries) > 0)
                    ({{count($entries)}})
                    <br/>
                    <ul>
                    @foreach ($entries as $entry)
                        <li>{{ $entry->player->id }}</li>
                    @endforeach
                    </ul>
                @else
                    - <em>no players yet</em>
                @endif
                <div class="text-center">
                    @if ($user)
                        @if ($user_entry)
                            <a href="{{"/tournaments/$tournament->id/unregister"}}" class="btn btn-danger"><i class="fa fa-minus-circle" aria-hidden="true"></i> Unregister</a>
                        @else
                            <a href="{{"/tournaments/$tournament->id/register"}}" class="btn btn-primary"><i class="fa fa-plus-circle" aria-hidden="true"></i> Register</a>
                        @endif
                    @endif
                </div>
            </p>
        </div>
    </div>
@stop

