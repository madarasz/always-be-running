@if (count($user->videos))
    <div class="bracket">
        <h5>
            <i class="fa fa-video-camera" aria-hidden="true"></i>
            Videos ({{ count($user->videos) }})
        </h5>

        @include('tournaments.viewer.videolist',
            ['id' => 'list-user-video', 'videos' => $user->videos()->orderBy('videos.created_at', 'desc')->get(),
            'user' => \Illuminate\Support\Facades\Auth::user(), 'profile' => true])

        {{--viewer--}}
        <div id="section-watch-video" class="hidden-xs-up text-xs-center">
            <hr/>
            <div id="section-video-player"></div>
            <button class="btn btn-danger btn-xs" onclick="watchVideo(false)" type="button">
                <i class="fa fa-window-close" aria-hidden="true"></i> Close
            </button>
        </div>
    </div>
@endif