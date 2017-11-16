<div class="tab-pane" id="tab-videos" role="tabpanel">
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
                <h5>
                    <i class="fa fa-video-camera" aria-hidden="true"></i>
                    Videos on approved tournaments
                </h5>
                <div class="row">
                    {{--videos by channel--}}
                    <div class="col-xs-12 col-md-6">
                        <table class="table table-sm table-striped abr-table" id="videos">
                            <thead>
                            <tr>
                                <th>channel name</th>
                                <th>number of videos</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($video_channels as $name => $count)
                                <tr>
                                    <td>{{ $name }}</td>
                                    <td>{{ $count }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{--videos by user--}}
                    <div class="col-xs-12 col-md-6">
                        <table class="table table-sm table-striped abr-table" id="videos">
                            <thead>
                            <tr>
                                <th>user</th>
                                <th>number of videos</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($video_users as $userid => $count)
                                <?php $vuser = App\User::findOrFail($userid); ?>
                                <tr>
                                    <td>
                                        <a href="/profile/{{ $vuser->id }}" class="{{ $vuser->linkClass() }}">
                                            {{ $vuser->displayUsername() }}
                                        </a>
                                    </td>
                                    <td>{{ $count }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row">
                {{--users by video tags--}}
                <div class="col-xs-12 col-md-6">
                    <div class="bracket">
                        <h5>
                            <i class="fa fa-user-circle-o" aria-hidden="true"></i>
                            Users tagged in videos
                        </h5>
                        <table class="table table-sm table-striped abr-table" id="tags">
                            <thead>
                            <tr>
                                <th>user tagged</th>
                                <th>number of videos</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($video_users_tagged as $userid => $count)
                                <?php $vuser = App\User::findOrFail($userid); ?>
                                <tr>
                                    <td>
                                        <a href="/profile/{{ $vuser->id }}" class="{{ $vuser->linkClass() }}">
                                            {{ $vuser->displayUsername() }}
                                        </a>
                                    </td>
                                    <td>{{ $count }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row">
                {{--Missing videos--}}
                <div class="col-xs-12">
                    <div class="bracket">
                        <h5>
                            <i class="fa fa-chain-broken" aria-hidden="true"></i>
                            Missing videos
                            <div class="small-text">
                                missing videos are hidden
                            </div>
                        </h5>
                        <a href="/admin/videos/broken" class="btn btn-primary">Detect missing</a>
                        <p>
                            Missing videos: {{ $missing_videos->count() }}
                        </p>
                        <table class="table table-sm table-striped abr-table" id="videos-missing">
                            <thead>
                            <tr>
                                <th></th>
                                <th>title</th>
                                <th>channel</th>
                                <th>tournament</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($missing_videos as $missing_video)
                                <tr>
                                    <td>
                                        @if ($missing_video->type == 1)
                                            <i class="fa fa-youtube-play" aria-hidden="true"></i>
                                        @else
                                            <i class="fa fa-twitch" aria-hidden="true"></i>
                                        @endif
                                    </td>
                                    <td>
                                        {{ substr($missing_video->video_title, 0, 40) }}
                                    </td>
                                    <td>
                                        @if ($missing_video->type == 1)
                                            {{ $missing_video->channel_name }}
                                        @else
                                            <a href="https://www.twitch.tv/{{ $missing_video->channel_name }}">
                                                {{ $missing_video->channel_name }}
                                            </a>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ $missing_video->tournament->seoUrl() }}">
                                            {{ $missing_video->tournament->title }}
                                        </a>
                                    </td>
                                    <td>
                                        {!! Form::open(['method' => 'DELETE', 'url' => "/videos/{$missing_video->id}"]) !!}
                                        {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i>',
                                            array('type' => 'submit', 'class' => 'btn btn-danger btn-xs delete-video',
                                            'onclick' => "return confirm('Are you sure you want to delete video?')")) !!}
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>