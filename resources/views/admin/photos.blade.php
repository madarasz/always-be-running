<div class="tab-pane" id="tab-photos" role="tabpanel">
    {{--Notification for approve--}}
    <div class="alert alert-warning view-indicator hidden-xs-up" id="notif-photo">
        <i class="fa fa-clock-o" aria-hidden="true"></i>
        You have photos waiting for approval.
    </div>
    <div class="row">
        {{--Pending photos--}}
        <div class="col-xs-12">
            <div class="bracket">
                <h5 id="pending-photo-title">
                    <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                    Pending photos
                </h5>
                @include('admin.gallery', ['photos' => $photos, 'approval' => null])
                <div id="no-approve-photo" class="small-text">no photos to approve</div>
            </div>
        </div>
        <div class="col-xs-12">
            <div class="bracket">
                <h5>
                    Photos on approved tournaments
                </h5>
                <div class="row">
                    {{--photos by channel--}}
                    <div class="col-xs-12 col-md-6">
                        <table class="table table-sm table-striped abr-table" id="videos">
                            <thead>
                            <tr>
                                <th>tournament name</th>
                                <th>number of photos</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($photo_tournaments as $tournament_id => $count)
                                <?php $vtournament = App\Tournament::findOrFail($tournament_id); ?>
                                <tr>
                                    <td>
                                        <a href="{{ $vtournament->seoUrl() }}">
                                            {{ $vtournament->title }}
                                        </a>
                                    </td>
                                    <td>{{ $count }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{--photos by user--}}
                    <div class="col-xs-12 col-md-6">
                        <table class="table table-sm table-striped abr-table" id="videos">
                            <thead>
                            <tr>
                                <th>user</th>
                                <th>number of photos</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($photo_users as $userid => $count)
                                <?php $vuser = App\User::findOrFail($userid); ?>
                                <tr>
                                    <td>
                                        <a href="/profile/{{ $vuser->id }}" {{ $vuser->supporter ? 'class=supporter' : '' }}>
                                            {{ $vuser->name }}
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
        {{--Approved photos--}}
        <div class="col-xs-12">
            <div class="bracket">
                <h5>
                    <i class="fa fa-thumbs-up" aria-hidden="true"></i>
                    Approved photos
                </h5>
                @include('admin.gallery', ['photos' => $photos, 'approval' => true])
            </div>
        </div>
    </div>
</div>