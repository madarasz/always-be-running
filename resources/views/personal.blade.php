@extends('layout.general')

@section('content')
    <h4 class="page-header">Personal</h4>
    @include('partials.message')
    @include('errors.list')
    @include('tournaments.modals.claim')
    <div class="row">
        <div class="col-md-4 col-xs-12">
            {{--My calendar--}}
            <div class="bracket">
                <h5>
                    <i class="fa fa-calendar" aria-hidden="true"></i>
                    My calendar<br/>
                    <small>tournaments I registered to</small>
                    @include('partials.calendar')
                </h5>
            </div>
        </div>
        <div class="col-md-8 col-xs-12">
            <div class="bracket">
                @include('tournaments.partials.tabledin',
                ['columns' => ['title', 'location', 'date', 'cardpool', 'user_claim'],
                'title' => 'My tournaments', 'subtitle' => 'tournaments I registered to',
                 'id' => 'my-table', 'icon' => 'fa-list-alt', 'loader' => true])
            </div>
        </div>
    </div>
    <script type="text/javascript">
        var calendardata = {};
        getTournamentData('foruser={{ $user->id }}', function(data) {
            $('.loader').addClass('hidden-xs-up');
            updateTournamentTable('#my-table', ['title', 'location', 'date', 'cardpool', 'user_claim'], 'no tournaments to show', '', data);
            updateTournamentCalendar(data);
            drawCalendar(calendardata);
        });
    </script>
@stop

