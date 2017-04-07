@extends('layout.general')

@section('content')
    {{--Conclude, transfer modals--}}
    @if ($user && ($user->admin || $user->id == $tournament->creator))
        @include('tournaments.modals.conclude')
        @include('tournaments.modals.transfer')
    @endif
    {{--Header--}}
    @include('tournaments.viewer.header')
    {{--Messages--}}
    @include('partials.message')
    <div class="row">
        <div class="col-md-4 col-xs-12">
            {{--Tournament info--}}
            @include('tournaments.viewer.info')
            {{--Statistics chart--}}
            @if ($tournament->concluded)
                @include('tournaments.viewer.statchart')
            @endif
            {{--QR code--}}
            @include('tournaments.viewer.qr')
        </div>
        {{--Standings and claims--}}
        <div class="col-md-8 col-xs-12">
            {{--Tournament description--}}
            @include('errors.list')
            @unless($tournament->description === '')
                @include('tournaments.viewer.description')
            @endunless
            {{--Matches--}}
            @if (file_exists('tjsons/'.$tournament->id.'.json'))
                @include('tournaments.viewer.matches')
            @endif
            {{--Photos and Videos--}}
            <div class="bracket">
                @include('tournaments.viewer.photos')
                @include('tournaments.viewer.videos')
                @if (!$user)
                    <hr/>
                    <div class="text-xs-center" id="suggest-login-media">
                        <a href="/oauth2/redirect">Login via NetrunnerDB</a> to add photos or videos.
                    </div>
                @endif
            </div>
            {{--Results--}}
            @include('tournaments.viewer.results')
        </div>
    </div>
    {{--Google maps library--}}
    @if($tournament->tournament_type_id != 7)
        @include('tournaments.viewer.map')
    @endif
@stop
