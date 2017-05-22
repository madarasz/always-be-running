@extends('layout.general')

@section('content')
    <a name="page-top" id="page-top"></a>
    <h4 class="page-header">
        Netrunner Tournaments Videos
        (<span id="label-all-videos"></span>)
    </h4>
    {{--Filters--}}
    {{--<div class="row">--}}
        {{--<div class="col-xs-12">--}}
            {{--<div class="bracket">--}}
                {{--<div class="row">--}}
                    {{--<div class="col-xs-12">--}}
                        {{--<h5 class="h5-filter"><i class="fa fa-filter" aria-hidden="true"></i> Filter</h5>--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--</div>--}}
        {{--</div>--}}
    {{--</div>--}}
    <div class="row hidden-xs-up" id="row-wide-player">
        <div class="col-xs-12">
            <div class="bracket" id="bracket-video-wide"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-md-8 col-lg-9 push-md-4 push-lg-3">
            <div class="bracket hidden-xs-up" id="bracket-video-narrow">
                <div id="content-video">
                    <div id="section-watch-video" class="hidden-xs-up text-xs-center">
                        <div id="section-video-player"></div>
                        <div id="tagged-users"></div>
                        <button class="btn btn-primary btn-xs hidden-sm-down" onclick="videoToWide(true)" id="button-wide">
                            <i class="fa fa-arrows-alt" aria-hidden="true"></i> Wide
                        </button>
                        <button class="btn btn-primary btn-xs hidden-xs-up hidden-sm-down" onclick="videoToWide(false)" id="button-narrow">
                            <i class="fa fa-compress" aria-hidden="true"></i> Normal
                        </button>
                        <button class="btn btn-danger btn-xs" onclick="watchVideo(false)">
                            <i class="fa fa-window-close" aria-hidden="true"></i> Close
                        </button>
                    </div>
                </div>
            </div>
            <div class="bracket" id="bracket-video-list">
                <h5>
                    <i class="fa fa-video-camera" aria-hidden="true"></i>
                    Videos <span id="label-videos-number"></span><span id="link-tournament" class="small-text"></span>
                </h5>
                <div id="helper-select" class="m-t-2 m-b-2 text-xs-center small-text">select a tournament</div>
                <table class="table table-sm table-striped abr-table" id="table-videos">
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-xs-12 col-md-4 col-lg-3 pull-md-8 pull-lg-9">
            <div class="bracket">
                <h5>
                    <i class="fa fa-list-alt" aria-hidden="true"></i>
                    Tournaments
                </h5>
                <table class="table table-striped hover-row" id="table-tournaments">
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        var allTournamentData, selectedTournamentData;

        loadTournamentsWithVideos();

        // check last used video player size
        if (getCookie('video-width') == 'wide') {
            videoToWide(true);
        }

        //create trigger to resizeEnd event
        $(window).resize(function() {
            if(this.resizeTO) clearTimeout(this.resizeTO);
            this.resizeTO = setTimeout(function() {
                $(this).trigger('resizeEnd');
            }, 500);
        });

        //resize video when window resize is completed
        $(window).on('resizeEnd', function() {
            resizeVideo();
        });

        function loadTournamentsWithVideos() {
            $.ajax({
                url: '/api/videos',
                dataType: "json",
                async: true,
                success: function (data) {
                    allTournamentData = data;
                    selectedTournamentData = allTournamentData.slice();
                    displayVideoTournamentList();
                }
            });
        }

        function displayVideoTournamentList() {
            var videoCounter = 0;
            console.log(getCookie('selected-tournament'));
            for (var i = 0; i < selectedTournamentData.length; i++) {
                videoCounter += selectedTournamentData[i].videos.length;

                $('#table-tournaments > tbody').append($('<tr>', {
                    onclick: 'showVideos('+i+'); setCookie("selected-video", "", 14);'
                }).append($('<td>', { id: 'cell-'+i })));

                $('#cell-'+i).append(
                        $('<div>', { class: 'featured-title' }),
                        $('<span>', { class: 'small-text' }),
                        $('<div>/', { class: 't-list-footer' })
                );

                // emblems
                tournamentEmblem($('#cell-'+i+' > div.featured-title'), selectedTournamentData[i].tournament_type.type_name,
                        selectedTournamentData[i].tournament_format.format_name);

                // title
                $('#cell-'+i+' > div.featured-title').append(selectedTournamentData[i].title);

                // date + cardpool
                $('#cell-'+i+' > span').text('('+selectedTournamentData[i].date+') - ' + selectedTournamentData[i].cardpool.name);

                // info
                var footer = $('#cell-'+i+' > div.t-list-footer');
                footer.append(selectedTournamentData[i].players_number, ' ',
                        $('<i>', { class: 'fa fa-user', title: 'players' }));
//                if (selectedTournamentData[i].claimNumber) {
//                    footer.append(' ', selectedTournamentData[i].claimNumber, ' ',
//                            $('<i>', { class: 'fa fa-address-card', title: 'claims' }));
//                }
                footer.append(' ', selectedTournamentData[i].videos.length, ' ',
                        $('<i>', { class: 'fa fa-video-camera', title: 'videos' }));
                footer.append(' ', selectedTournamentData[i].location_country);

                // remember last selected tournament
                if (getCookie('selected-tournament') == selectedTournamentData[i].id) {
                    showVideos(i);
                }
            }
            // remember last selected video
            if (getCookie('selected-video').length) {
                watchVideo(getCookie('selected-video'));
            }
            $('#label-all-videos').text(videoCounter);


        }

        function showVideos(rowNum) {
            $('#table-videos > tbody').empty(); // empty video list
            $('#table-tournaments > tbody > tr > td').removeClass('row-selected'); // remove previous tournament selection
            $('#cell-'+rowNum).addClass('row-selected'); // add tournament selection
            var videos = selectedTournamentData[rowNum].videos;
            $('#helper-select').text('select a video').removeClass('hidden-xs-up'); // display helper text
            $('#section-watch-video').addClass('hidden-xs-up'); // hide video
            $('#label-videos-number').text('('+videos.length+')');  // show video number
            // show narrow video player bracket, move helper there
            if (getCookie('video-width') != 'wide') {
                $('#bracket-video-narrow').removeClass('hidden-xs-up').append($('#helper-select'));
            }
            // tournament info
            $('#link-tournament').empty().append(' - ', $('<a>', {
                href: selectedTournamentData[rowNum].seoUrl,
                text: selectedTournamentData[rowNum].title }
            ), ' ('+selectedTournamentData[rowNum].date+') - '+selectedTournamentData[rowNum].cardpool.name);
            // scroll to video
            $('html, body').animate({
                scrollTop: $("#helper-select").offset().top - 100
            }, 500);

            setCookie('selected-tournament', selectedTournamentData[rowNum].id ,14); // remember selected tournament

            // display videos
            for (var i = 0; i < videos.length; i++) {
                $('#table-videos > tbody').append($('<tr>', { id: 'video-'+videos[i].video_id }).append($('<td>').append($('<a>', {
                    href: '#',
                    onclick: "watchVideo('"+videos[i].video_id+"')"
                }).append($('<img>', {
                    src: 'https://i.ytimg.com/vi/'+videos[i].video_id+'/default.jpg'
                }))), $('<td>', { id: 'desc-' + i})));

                var descr = $('#desc-'+i)
                    .append('<b><a href="#" onclick="watchVideo(\''+videos[i].video_id+'\')">'+videos[i].video_title+'</a></b>')
                    .append('<br>'+videos[i].channel_name+'<br>');

                if (videos[i].video_tags.length) {
                    descr.append($('<span>', { id: 'tags-' + videos[i].video_id }));
                    var taginfo = $('#tags-' + videos[i].video_id);
                }
                for (var u = 0; u < videos[i].video_tags.length; u++) {
                    var tag = videos[i].video_tags[u];
                    // deck ids
                    if (tag.entry && parseInt(tag.is_runner) != 0) {
                        taginfo.append('<a href="'+tag.entry.runnerDeckUrl+'"><img src="/img/ids/' + tag.entry.runner_deck_identity + '.png"/></a> ');
                    }
                    if (tag.entry && parseInt(tag.is_runner) != 1) {
                        taginfo.append('<a href="'+tag.entry.corpDeckUrl+'"><img src="/img/ids/' + tag.entry.corp_deck_identity + '.png"/></a> ');
                    }
                    // username
                    taginfo.append('<a href="/profile/'+tag.user.id+'">'+tag.user.name+'</a>');
                    // side
                    if (parseInt(tag.is_runner) == 1) {
                        taginfo.append(' <span class="small-text">(runner)</span>');
                    } else if (parseInt(tag.is_runner) == 0) {
                        taginfo.append(' <span class="small-text">(corporation)</span>');
                    }
                    if (u < videos[i].video_tags.length - 1) {
                        taginfo.append(' - ');
                    }
                }
            }
        }

        function videoToWide(wide) {
            if (wide) {
                $('#row-wide-player').removeClass('hidden-xs-up');
                $('#bracket-video-narrow').addClass('hidden-xs-up');
                $('#button-narrow').removeClass('hidden-xs-up');
                $('#button-wide').addClass('hidden-xs-up');
                $('#bracket-video-wide').append($('#content-video')).append($('#helper-select'));
                resizeVideo();
                setCookie('video-width', 'wide', 14);
            } else {
                $('#row-wide-player').addClass('hidden-xs-up');
                $('#bracket-video-narrow').removeClass('hidden-xs-up').append($('#content-video')).append($('#helper-select'));
                $('#button-narrow').addClass('hidden-xs-up');
                $('#button-wide').removeClass('hidden-xs-up');
                resizeVideo();
                setCookie('video-width', 'narrow', 14);
            }
            $('html, body').animate({
                scrollTop: $("#section-watch-video").offset().top - 60
            }, 500);
        }

    </script>
@stop