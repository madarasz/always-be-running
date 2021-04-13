@extends('layout.general')

@section('content')
    {{--Conclude, transfer modals--}}
    @if ($user && ($user->admin || $user->id == $tournament->creator))
        @include('tournaments.modals.transfer')
    @endif
    @if ($user)
        @include('tournaments.modals.conclude')
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
            {{--Tournament Groups--}}
            @foreach($groups as $group)
                @include('tournaments.viewer.group', ['group' => $group])
            @endforeach
            {{--References--}}
            @include('tournaments.viewer.references')
        </div>
        {{--Standings and claims--}}
        <div class="col-md-8 col-xs-12">
            {{--Tournament description--}}
            @include('errors.list')
            @unless($tournament->description === '')
                @include('tournaments.viewer.description')
            @endunless
            {{--Prizes--}}
            @if(@$tournament->prize || $tournament->prize_additional !== '' || $tournament->unofficial_prizes->count() > 0)
                @include('tournaments.viewer.prizes')
            @endif
            {{--Matches--}}
            @if (file_exists('tjsons/'.$tournament->id.'.json') && $tournament->concluded)
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
    @if(!$tournament->online)
        @include('tournaments.viewer.map')
    @endif
    {{--Enable gallery--}}
    <script type="application/javascript">
        $(document).on('click', '[data-toggle="lightbox"]', function(event) {
            event.preventDefault();
            $(this).ekkoLightbox({alwaysShowClose: true});
        });
    </script>
@stop
