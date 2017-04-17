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
        </div>
        <div class="col-md-6 col-xs-12">
            <div class="bracket">
                <p>
                    <strong>For competitive players:</strong>
                </p>
                @foreach($badges as $badge)
                    @if ($badge->order > 100 && $badge->order < 1000)
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
    @include('partials.legal-icons')
@stop
