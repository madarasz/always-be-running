@extends('layout.general')

@section('content')
    <h4 class="page-header">Badges</h4>
    <div class="row">
        <div class="col-md-6 col-xs-12">
            <div class="bracket">
                <p>
                    <strong>For all players:</strong>
                </p>
                @foreach($badges as $badge)
                    @if ($badge->order > 2000 && $badge->order < 3000)
                        @include('partials.badgelist')
                    @endif
                @endforeach
            </div>
            <div class="bracket">
                <p>
                    <strong>For tournament organizers:</strong>
                </p>
                @foreach($badges as $badge)
                    @if ($badge->order > 3000 && $badge->order < 4000)
                        @include('partials.badgelist')
                    @endif
                @endforeach
            </div>
        </div>
        <div class="col-md-6 col-xs-12">
            <div class="bracket">
                <p>
                    <strong>For competative players:</strong>
                </p>
                @foreach($badges as $badge)
                    @if ($badge->year)
                        @include('partials.badgelist')
                    @endif
                @endforeach
            </div>
            <div class="bracket">
                <p>
                    <strong>For heavy NetrunnerDB users:</strong><br/>
                    <span class="small-text">needs relogin to refresh</span>
                </p>
                @foreach($badges as $badge)
                    @if ($badge->order > 4000)
                        @include('partials.badgelist')
                    @endif
                @endforeach
                <div class="small-text">
                    You get +1 reputation for each like on your decklists and reviews, +5 for each favorite.
                </div>
            </div>
            <div class="bracket">
                <p>
                    <strong>Other badges:</strong>
                </p>
                @foreach($badges as $badge)
                    @if ($badge->order < 100)
                        @include('partials.badgelist')
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    {{--Flaticon legal--}}
    <div class="row">
        <div class="col-xs-12 text-xs-center legal-bullshit">
            Some of these icons are made by&nbsp;
            <a href="http://okodesign.ru/">Elias Bikbulatov</a>,
            <a href="http://www.freepik.com/">Freepik</a>,
            <a href="http://simpleicon.com/">SimpleIcon</a>
            &nbsp;from&nbsp; <a href="http://www.flaticon.com/">http://www.flaticon.com/</a>.
        </div>
    </div>
@stop

