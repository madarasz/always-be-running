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
                    <a class="btn btn-success btn-xs pull-right m-r-1 disabled" id="button-approve-all-photos"
                       href="/photos/0/approve-all">
                        <i class="fa fa-thumbs-up" aria-hidden="true"></i> Approve all
                    </a>
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
                            @foreach($photo_tournaments as $photo_tournament)
                                <tr>
                                    <td>
                                        <a href="{{ '/tournaments/'.$photo_tournament->tournament_id }}">
                                            {{ $photo_tournament->title }}
                                        </a>
                                    </td>
                                    <td>{{ $photo_tournament->total }}</td>
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
                            @if ($photo_users)
                                @foreach($photo_users as $photo_user)
                                    <tr>
                                        <td>
                                            <a href="/profile/{{ $photo_user->id }}" class="{{ $photo_user->abr_link_class }}">
                                                {{ $photo_user->display_name }}
                                            </a>
                                        </td>
                                        <td>{{ $photo_user->total }}</td>
                                    </tr>
                                @endforeach
                            @endif
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
                    Latest approved photos
                </h5>
                @include('admin.gallery', ['photos' => $photos, 'approval' => true])
            </div>
        </div>
    </div>
</div>
