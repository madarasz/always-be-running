@extends('layout.general')

@section('content')
    <h4 class="page-header m-b-0">Personal</h4>
    @include('partials.message')
    @include('errors.list')

    {{--Tabs--}}
    <div class="modal-tabs">
        <ul id="admin-tabs" class="nav nav-tabs" role="tablist">
            <li class="nav-item notif-red notif-badge" id="tabf-tournament">
                <a class="nav-link active" data-toggle="tab" href="#tab-tournaments" role="tab">
                    <i class="fa fa-list-alt" aria-hidden="true"></i>
                    Tournaments
                </a>
            </li>
            <li class="nav-item notif-red notif-badge" id="tabf-photo">
                <a class="nav-link" data-toggle="tab" href="#tab-photos" role="tab">
                    <i class="fa fa-camera" aria-hidden="true"></i>
                    Photos
                </a>
            </li>
            <li class="nav-item notif-red notif-badge" id="tabf-video">
                <a class="nav-link" data-toggle="tab" href="#tab-videos" role="tab">
                    <i class="fa fa-video-camera" aria-hidden="true"></i>
                    Videos
                </a>
            </li>
        </ul>
    </div>

    {{--Tab pages--}}
    <div class="tab-content">
        <div class="tab-pane active" id="tab-tournaments" role="tabpanel">
            @include('personal.tournaments')
        </div>
        <div class="tab-pane" id="tab-photos" role="tabpanel">
            @include('personal.photos')
        </div>
        <div class="tab-pane" id="tab-videos" role="tabpanel">
            @include('personal.videos')
        </div>
    </div>

    <script type="text/javascript">
        var calendardata = {}, map, bounds, infowindow;
        function initializeMap() {
            map = new google.maps.Map(document.getElementById('mymap'), {
                zoom: 1,
                center: {lat: 40.157053, lng: 19.329297}
            });
            infowindow = new google.maps.InfoWindow();
            bounds = new google.maps.LatLngBounds();
            getTournamentData('?foruser={{ $user->id }}&desc=1', function (data) {
                $('.loader').addClass('hidden-xs-up');
                updateTournamentTable('#my-table', ['title', 'location', 'date', 'cardpool', 'user_claim'], 'no tournaments to show', '', data);
                updateTournamentCalendar(data);
                drawCalendar(calendardata);
                clearMapMarkers(map);
                codeAddress(data, map, bounds, infowindow);
            });
        }
        // enable gallery
        $(document).on('click', '[data-toggle="lightbox"]', function(event) {
            event.preventDefault();
            $(this).ekkoLightbox({alwaysShowClose: true});
        });
    </script>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key={{ENV('GOOGLE_MAPS_API')}}&callback=initializeMap&libraries=geometry">
    </script>
@stop

