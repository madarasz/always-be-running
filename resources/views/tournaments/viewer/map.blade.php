{{--Draw location map--}}
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key={{ENV('GOOGLE_MAPS_API')}}&libraries=places&callback=initializeMap">
</script>
{{--Scripts for google maps--}}
<script type="text/javascript">
    var map, marker;

    function initializeMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 1,
            center: {lat: 40.157053, lng: 19.329297},
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            streetViewControl: false,
            mapTypeControl: false
        });

        marker = new google.maps.Marker({
            map: map,
            anchorPoint: new google.maps.Point(0, -29)
        });

        var service = new google.maps.places.PlacesService(map);
        service.getDetails({placeId: '{{ $tournament->location_place_id }}'}, function(place, status){
            if (status === google.maps.places.PlacesServiceStatus.OK) {
                renderPlace(place, marker, map)
            }
        });
    }
</script>