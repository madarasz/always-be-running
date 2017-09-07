@extends('layout.general')

@section('content')
    <h4 class="page-header m-b-0">Administration</h4>
    @include('partials.message')
    @include('errors.list')

    {{--Conclude modal--}}
    @include('tournaments.modals.conclude')

    {{--Tabs--}}
    <div class="modal-tabs">
        <ul id="admin-tabs" class="nav nav-tabs" role="tablist">
            <li class="nav-item notif-red notif-badge" id="tabf-tournament">
                <a class="nav-link active" data-toggle="tab" href="#tab-tournaments" role="tab">
                    <i class="fa fa-list-alt" aria-hidden="true"></i>
                    Tournaments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-entries" role="tab">
                    <i class="fa fa-list-ol" aria-hidden="true"></i>
                    Entries
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-packs" role="tab">
                    <i class="fa fa-cubes" aria-hidden="true"></i>
                    Packs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-badges" role="tab">
                    <i class="fa fa-child" aria-hidden="true"></i>
                    Badges
                </a>
            </li>
            <li class="nav-item notif-red notif-badge" id="tabf-photo">
                <a class="nav-link" data-toggle="tab" href="#tab-photos" role="tab">
                    <i class="fa fa-camera" aria-hidden="true"></i>
                    Photos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-videos" role="tab">
                    <i class="fa fa-video-camera" aria-hidden="true"></i>
                    Videos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-stats" role="tab">
                    <i class="fa fa-line-chart" aria-hidden="true"></i>
                    Stats
                </a>
            </li>
            @if(\Illuminate\Support\Facades\Auth::user()->id == 1276)
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#tab-vip" role="tab">
                        <i class="fa fa-star" aria-hidden="true"></i>
                        VIP
                    </a>
                </li>
            @endif
        </ul>
    </div>

    {{--Tab panes--}}
    <div class="tab-content">
        {{--Tournaments--}}
        @include('admin.tournaments')
        {{--Entries--}}
        @include('admin.entries')
        {{--Packs--}}
        @include('admin.packs')
        {{--Badges--}}
        @include('admin.badges')
        {{--Photos--}}
        @include('admin.photos')
        {{--Videos--}}
        @include('admin.videos')
        {{--Stats--}}
        @include('admin.stats')
        {{--VIP--}}
        @if(\Illuminate\Support\Facades\Auth::user()->id == 1276)
            @include('admin.vip')
        @endif
    </div>
    {{--screen size checker--}}
    <p>
        viewing on screensize:
        <strong>
        <span class="hidden-sm-up">xs</span>
        <span class="hidden-xs-down hidden-md-up">sm</span>
        <span class="hidden-sm-down hidden-lg-up">md</span>
        <span class="hidden-md-down hidden-xl-up">lg</span>
        <span class="hidden-lg-down">xl</span>
        </strong>
    </p>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
        // activate tabs
        $('#admin-tabs a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
        });

        // charts
        var entryTypes = [['type', 'count'],
            @foreach($entry_types as $type => $count)
                ['{{$type}}', {{$count}}],
            @endforeach
        ];
        google.charts.load('current', {
            'packages':['corechart', 'geochart'],
            'mapsApiKey': '{{ env('GOOGLE_MAPS_API') }}'
        });

        // get tournament data
        getTournamentData("?approved=null", function(data) {
            updateTournamentTable('#pending', ['title', 'date', 'cardpool', 'approval', 'conclusion', 'players', 'decks',
                'action_edit', 'action_approve', 'action_reject', 'action_delete'], 'no pending tournaments', '{{ csrf_token() }}', data);
            getTournamentData("?approved=0", function(data) {
                updateTournamentTable('#rejected', ['title', 'date', 'cardpool', 'approval', 'conclusion', 'players', 'decks',
                    'action_edit', 'action_approve', 'action_delete'], 'no rejected tournaments', '{{ csrf_token() }}', data);
                getTournamentData("?conflict=1", function(data) {
                    updateTournamentTable('#conflict', ['title', 'date', 'type', 'creator', 'approval', 'players', 'claims', 'action_delete'],
                            'no tournaments with conflicts', '{{ csrf_token() }}', data);
                    getTournamentData("?approved=1&concluded=0&recur=0&end={{ $nowdate }}", function(data) {
                        updateTournamentTable('#late', ['title', 'date', 'location', 'creator', 'conclusion', 'players', 'action_delete'],
                                'no late tournaments', '{{ csrf_token() }}', data);
                        getTournamentData("?deleted=1", function(data) {
                            updateTournamentTable('#deleted', ['title', 'date', 'creator', 'approval', 'conclusion', 'players', 'decks',
                                'action_edit', 'action_restore', 'action_purge'], 'no deleted tournaments', '{{ csrf_token() }}', data);
                            getTournamentData("?incomplete=1", function(data) {
                                updateTournamentTable('#incomplete', ['title', 'date', 'location', 'cardpool', 'creator', 'players',
                                    'created_at', 'action_edit', 'action_purge'], 'no incomplete items', '{{ csrf_token() }}', data);
                                drawAdminChart(entryTypes);
                            });
                        });
                    });
                });
            });
        });

        // enable gallery
        $(document).on('click', '[data-toggle="lightbox"]', function(event) {
            event.preventDefault();
            $(this).ekkoLightbox({alwaysShowClose: true});
        });
    </script>
@stop

