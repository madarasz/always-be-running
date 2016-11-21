{{--Transfer tournament modal--}}
<div class="modal fade" id="transferModal" tabindex="-1" role="dialog" aria-labelledby="transfer modal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    Transfer tournament<br/>
                    <div class="modal-subtitle" id="modal-subtitle">{{ $tournament->title }}</div>
                </h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid bd-example-row">
                    <div class="text-xs-center">
                        <p>
                            This will transfer the tournament ownership to another user.<br/>
                            <div class="small-text p-b-1">
                                You can only choose from users who have logged into AlwaysBeRunning.net at least once.<br/>
                                <br/>
                                <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                WARNING: You will not be able to undo this, unless you are an admin.
                            </div>
                        </p>
                        {!! Form::open(['url' => "/tournaments/$tournament->id/transfer", 'method' => 'PATCH',
                            'class' => 'form-inline', 'id' => 'form-transfer']) !!}
                            <div class="form-group">
                                {!! Form::label('creator', 'new owner:') !!}
                                {!! Form::select('creator', $all_users, null, ['class' => 'form-control']) !!}
                            </div>
                                <button type="submit" class="btn btn-primary" id="submit-transfer">
                                    Transfer tournament
                                </button>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>