{{--Videos--}}
<h5 class="m-t-2">
    <i class="fa fa-video-camera" aria-hidden="true"></i>
    Videos
    @if (count($tournament->videos))
        <span class="user-counts">({{ count($tournament->videos) }})</span>
    @else
        <span class="user-counts">- no videos yet</span>
    @endif
    @if ($user)
        <button class="btn btn-primary btn-xs pull-right" id="button-add-videos"
                onclick="toggleVideoAdd(true)">
            <i class="fa fa-video-camera" aria-hidden="true"></i> Add videos
        </button>
        <button class="btn btn-primary btn-xs hidden-xs-up pull-right" id="button-done-videos"
                onclick="toggleVideoAdd(false)">
            <i class="fa fa-check" aria-hidden="true"></i> Done
        </button>
    @endif
</h5>
{{--Add video--}}
@if ($user)
    <div id="section-add-videos" class="hidden-xs-up text-xs-center card-darker m-t-1 p-b-1">
        <hr/>
        <div class="p-b-1">
            Add a Youtube video
        </div>
        {!! Form::open(['method' => 'POST', 'url' => "/videos",
            'class' => 'form-inline']) !!}
        {!! Form::hidden('tournament_id', $tournament->id) !!}
        <div class="form-group">
            {!! Form::label('video_id', 'Youtube Video ID or URL', ['class' => 'small-text']) !!}
            {!! Form::text('video_id', '', ['class' => 'form-control']) !!}
        </div><br/>
        {!! Form::button('Add video', array('type' => 'submit',
            'class' => 'btn btn-success btn-xs', 'id' => 'button-add-video')) !!}
        {!! Form::close() !!}
    </div>
    <hr/>
@endif
{{--List of videos--}}
@if (count($tournament->videos) > 0)
    @include('tournaments.viewer.videolist',
        ['videos' => $tournament->videos, 'creator' => $tournament->creator, 'id' => 'videos'])
@endif
<div id="section-watch-video" class="hidden-xs-up text-xs-center">
    <hr/>
    <div id="section-video-player"></div>
    <button class="btn btn-danger btn-xs" onclick="watchVideo(false)">
        <i class="fa fa-window-close" aria-hidden="true"></i> Close
    </button>
</div>