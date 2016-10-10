{{--Conclude tournament modal--}}
<div class="modal fade" id="concludeModal" tabindex="-1" role="dialog" aria-labelledby="conclude modal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    Conclude tournament<br/>
                    <div class="modal-subtitle" id="modal-subtitle"></div>
                </h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid bd-example-row">
                    <div class="row">
                        <div class="row-height">
                            {{--Enter numbers manually--}}
                            <div class="col-md-6 col-xs-12 col-sm-height section-manual">
                                <div class="card inside-full-height">
                                    <div class="card-block text-xs-center">
                                        <div class="card-title">Enter number of players</div>
                                        {!! Form::open(['url' => '', 'id' => 'conclude-manual']) !!}
                                        <div class="text-xs-left">
                                            {{--Player number--}}
                                            <div class="form-group">
                                                {!! Html::decode(Form::label('players_number', 'Number of players<sup class="text-danger">*</sup>')) !!}
                                                {!! Form::text('players_number', '',
                                                     ['class' => 'form-control', 'placeholder' => 'number of players', 'required' => '']) !!}
                                            </div>
                                            {{--Top cut number--}}
                                            <div class="form-group">
                                                {!! Form::label('top_number', 'Number of players in top cut') !!}
{{--                                                {!! Form::text('top_number', '',--}}
{{--                                                     ['class' => 'form-control', 'placeholder' => 'number fo players in top cut']) !!}--}}
                                                {!! Form::select('top_number',
                                                    ['0' => '- no elimination rounds -', '4' => 'top 4', '8' => 'top 8', '16' => 'top 16'],
                                                    '', ['class' => 'form-control']) !!}
                                            </div>
                                        </div>
                                        <div class="button-spacer"></div>
                                        <div class="inside-bottom-center">
                                            {!! Form::submit('Conclude manually', ['class' => 'btn btn-conclude']) !!}
                                        </div>
                                        {!! Form::close() !!}

                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 text-xs-center hidden-md-up section-manual">
                                <strong>OR</strong>
                            </div>
                            {{--Import via NRTM--}}
                            <div class="col-md-6 col-xs-12 col-sm-height">
                                <div class="card inside-full-height">
                                    <div class="card-block text-xs-center">
                                        <div class="card-title">Import NRTM results</div>
                                        {!! Form::open(['url' => '', 'files' => true, 'id' => 'conclude-nrtm']) !!}
                                            {{--TODO: mandatory field--}}
                                            <input id="jsonresults" name="jsonresults" type="file" style="max-width: 100%;" required>
                                            <div class="button-spacer"></div>
                                            <div class="inside-bottom-center">
                                                {!! Form::submit('Conclude via import', ['class' => 'btn btn-conclude']) !!}
                                            </div>
                                        {!! Form::close() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{--Script to fill tournament conclude modal--}}
<script type="text/javascript">
    $('#concludeModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var subtitle = button.data('subtitle'), // module subtitle
                id = button.data('tournament-id'),  // tournament ID
                hide = button.data('hide-manual');  // if manual conclude part is hidden
        var modal = $(this);
        modal.find('.modal-subtitle').text(subtitle);
        modal.find('#conclude-manual').attr("action", "/tournaments/" + id + "/conclude/manual");
        modal.find('#conclude-nrtm').attr("action", "/tournaments/" + id + "/conclude/nrtm");
        // if manual part needs to be hidden
        if (hide == true) {
            modal.find('.section-manual').addClass('hidden-xs-up');
        } else {
            modal.find('.section-manual').removeClass('hidden-xs-up');
        }
    })
</script>