{{--Video tagging modal--}}
<div class="modal fade" id="videoTaggingModal" tabindex="-1" role="dialog" aria-labelledby="transfer modal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    Tag user in video<br/>
                    <div class="modal-subtitle" id="modal-subtitle"></div>
                </h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid bd-example-row">
                    <div class="text-xs-center">
                        {!! Form::open(['url' => '', 'method' => 'POST', 'class' => 'form-inline', 'id' => 'form-video-tag']) !!}
                            <div class="form-group">
                                {!! Form::label('user', 'user:') !!}
                                @include('partials.popover', ['direction' => 'top', 'content' =>
                                    'You can only tag users who have logged into <em>AlwaysBeRunning.net</em> at least once.'])
                                <select name="user_id" class="form-control">
                                    @if($regcount)
                                        <optgroup label="registered users">
                                            @foreach ($entries as $entry)
                                                @if ($entry->player)
                                                    <option value="{{ $entry->player->id }}">{{ $entry->player->displayUsername() }}</option>
                                                @endif
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="all users">
                                    @endif
                                    @foreach($all_users as $t_id => $t_user)
                                        <option value="{{ $t_id }}">{{ $t_user }}</option>
                                    @endforeach
                                    @if($regcount)
                                        </optgroup>
                                    @endif
                                </select>
{{--                                {!! Form::select('user', $all_users, null, ['class' => 'form-control']) !!}--}}
                            </div>
                            <button type="submit" class="btn btn-primary" id="submit-tag">
                                Tag user
                            </button>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{--Script to fill video tagging modal--}}
<script type="text/javascript">
    $('#videoTaggingModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var videoTitle = button.data('video-title'), // video title
                videoId = button.data('video-id');  // video ID
        var modal = $(this);
        modal.find('.modal-subtitle').text(videoTitle);
        modal.find('#form-video-tag').attr("action", "/videos/" + videoId + "/tag");
    }).on('hide.bs.modal', function () {
        $('.popover').popover('hide');
    });
</script>