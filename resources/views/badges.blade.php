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
            <div class="bracket">
                <p>
                    <strong>For supporters:</strong><br/>
                    <span class="legal-bullshit">visit <a href="/support-me">Support me</a> for more info</span>
                </p>
                @foreach($badges as $badge)
                    @if ($badge->order > 9000)
                        @include('partials.badgelist')
                    @endif
                @endforeach
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
        <div class="col-md-6 col-xs-12">
            <div class="bracket">
                <p>
                    <strong>For competitive players:</strong>
                </p>
                {{--Worlds--}}
                @include('partials.badgelist-year', ['badge_list' => $badges_worlds_winner])
                @include('partials.badgelist-year', ['badge_list' => $badges_worlds_top16])
                @include('partials.badgelist-year', ['badge_list' => $badges_worlds_player])
                {{--Europe Championship--}}
                @include('partials.badgelist-year', ['badge_list' => $europe_winner])
                @include('partials.badgelist-year', ['badge_list' => $europe_top16])
                @include('partials.badgelist-year', ['badge_list' => $europe_player])
                {{--North American Championship--}}
                @include('partials.badgelist-year', ['badge_list' => $namerica_winner])
                @include('partials.badgelist-year', ['badge_list' => $namerica_top16])
                @include('partials.badgelist-year', ['badge_list' => $namerica_player])
                {{--Nationals--}}
                @include('partials.badgelist-year', ['badge_list' => $nationals_winner])
                @include('partials.badgelist-year', ['badge_list' => $nationals_top])
                {{--Regionals--}}
                @include('partials.badgelist-year', ['badge_list' => $regionals_winner])
                @include('partials.badgelist-year', ['badge_list' => $regionals_top])
                {{--Other--}}
                @foreach($badges as $badge)
                    @if ($badge->order > 400 && $badge->order < 1000)
                        @include('partials.badgelist')
                    @endif
                @endforeach
            </div>
            <div class="bracket">
                <p>
                    <strong>Faction mastery:</strong><br/>
                </p>
                @foreach($badges as $badge)
                    @if ($badge->order > 5000 && $badge->order < 6000)
                        @include('partials.badgelist')
                    @endif
                @endforeach
            </div>
            <div class="bracket">
                <p>
                    <strong>National community awards</strong>
                </p>
                @foreach($badges as $badge)
                    @if ($badge->order > 8000 && $badge->order < 8999)
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
                    @if ($badge->order > 4000 && $badge->order < 5000)
                        @include('partials.badgelist')
                    @endif
                @endforeach
                <div class="small-text">
                    You get +1 reputation for each like on your decklists and reviews, +5 for each favorite.
                </div>
            </div>
        </div>
    </div>
    {{--Flaticon legal--}}
    @include('partials.legal-icons')
@stop
