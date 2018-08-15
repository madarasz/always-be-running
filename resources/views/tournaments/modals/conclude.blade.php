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
                            {{--Import--}}
                            <div class="col-md-6 col-xs-12 col-sm-height">
                                <div class="card inside-full-height">
                                    <div class="card-block text-xs-center">
                                        <div class="card-title">
                                            Import results
                                            <div class="small-text">Having trouble? Read the <a href="/faq#import" target="_blank">F.A.Q.</a></div>
                                        </div>
                                        {!! Form::open(['url' => '', 'files' => true, 'id' => 'conclude-nrtm']) !!}
                                            {{--Conclusion code--}}
                                            <div class="form-group text-xs-left">
                                                {!! Html::decode(Form::label('conclusion_code', 'Conclusion code')) !!}
                                                {!! Form::text('conclusion_code', null, ['class' => 'form-control', 'placeholder' => 'provided by NRTM']) !!}
                                            </div>
                                            <div><strong>OR</strong></div>
                                            {{--JSON--}}
                                            <div class="form-group text-xs-left">
                                                {!! Form::label('jsonresults', 'Results JSON file') !!}
                                                <input id="jsonresults" class="form-control" name="jsonresults" type="file">
                                            </div>
                                            <div class="p-b-1"><strong>OR</strong></div>
                                            {{--CSV--}}
                                            <div class="form-group text-xs-left">
                                                {!! Form::label('csvresults', 'CSV file') !!}
                                                @include('partials.popover', ['direction' => 'top', 'content' =>
                                                    '<strong>required row format:</strong><br/>
                                                    <em>name;swiss-rank;topcut-rank;runnerID;corpID</em><br/>
                                                    <br/>
                                                    If there were no top-cut or the player did not reach top-cut, use "0" (zero)
                                                    in the <em>top-cut rank</em> field. The ID fields should be the (substring of the)
                                                    official card name. Eg. "Andromeda" works.'])
                                                <input id="csvresults" class="form-control" name="csvresults" type="file">
                                            </div>
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
    }).on('hide.bs.modal', function (event) {
        $('.popover').popover('hide');
    });
</script>