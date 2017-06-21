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
            <div class="bracket">
                <h5>
                    <i class="fa fa-user-circle-o" aria-hidden="true"></i>
                    Users tagged in videos
                </h5>
                <div class="row">
                    {{--users by video tags--}}
                    <div class="col-xs-12 col-md-6">
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
        </div>
    </div>
</div>