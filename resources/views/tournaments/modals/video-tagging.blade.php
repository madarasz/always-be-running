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
                    {!! Form::open(['url' => '', 'method' => 'POST', 'class' => '', 'id' => 'form-video-tag']) !!}
                        <div class="row">
                            <div class="col-xs-12 col-md-5">
                                <div class="form-group">
                                    {!! Form::label('user', 'user:') !!}
                                    @include('partials.popover', ['direction' => 'top', 'content' =>
                                        'You can only tag users who have logged into <em>AlwaysBeRunning.net</em> at least once.'])
                                    <select name="user_id" class="form-control" id="user_id">
                                        @if($regcount)
                                            <optgroup label="users taking part">
                                                @foreach ($registered as $player)
                                                    <option value="{{ $player->id }}">{{ $player->displayUsername() }}</option>
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
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-2 flex-center">
                                <strong>-OR-</strong>
                            </div>
                            <div class="col-xs-12 col-md-5">
                                <div class="form-group">
                                    {!! Form::label('import_player_name', 'player name:') !!}
                                    @include('partials.popover', ['direction' => 'right', 'content' =>
                                                "use if you don't know the username"])
                                    {!! Form::text('import_player_name', null,
                                         ['class' => 'form-control', 'placeholder' => 'name', 'maxlength' => 50,
                                         'onkeydown' => 'enableUserField()', 'oninput' => 'enableUserField()', 'onpaste' => 'enableUserField()']) !!}
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-5">
                                <div class="form-group">
                                    {!! Form::label('side', 'side:') !!}
                                    <select name="side" id="side" class="form-control">
                                        <option value="">---</option>
                                        <option value="1">runner</option>
                                        <option value="0">corporation</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-7 flex-center">
                                <button type="submit" class="btn btn-primary" id="submit-tag">
                                    Tag user
                                </button>
                            </div>
                        </div>
                    {!! Form::close() !!}
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

    function enableUserField() {
        if (document.getElementById('import_player_name').value.length > 0) {
            $('#user_id').prop('disabled', true);
        } else {
            $('#user_id').prop('disabled', false);
        }
    }
</script>