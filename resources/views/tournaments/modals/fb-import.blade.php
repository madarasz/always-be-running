{{--Create tournament from Facebook import modal--}}
<div class="modal fade" id="fbImportModal" tabindex="-1" role="dialog" aria-labelledby="FB import modal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    Create tournament<br/>
                    <div class="modal-subtitle" id="modal-subtitle">from Facebook event</div>
                </h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid bd-example-row">
                    <div class="text-xs-center">
                        {!! Form::open(['url' => "/api/fb/import", 'method' => 'POST',
                            'class' => 'form-inline', 'id' => 'form-transfer']) !!}
                            <div class="form-group">
                                {!! Form::label('creator', 'FB event:') !!}
                                @include('partials.popover', ['direction' => 'top', 'content' =>
                                    'Provide either the event ID <em>(long number at the end of URL)</em> or the
                                    full event URL <em>(https://www.facebook.com/events/1327206280634583/)</em>.'])
                                {!! Form::text('event', null, ['class' => 'form-control', 'placeholder' => 'ID or URL',
                                    'id'=>'event']) !!}
                            </div>
                            <button type="submit" class="btn btn-primary disabled" id="submit-import">
                                Create tournament
                            </button>
                        {!! Form::close() !!}
                        <div id="title-event" class="hidden-xs-up">
                            <i class="fa fa-check text-success" aria-hidden="true"></i>
                            <span id="text-event-name" class="small-text"></span>
                        </div>
                        <div id="error-event" class="hidden-xs-up text-danger">
                            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                            <span class="small-text">Event not found. Maybe event is not 'public'.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    // hide pop-overs
    $('#fbImportModal').on('hide.bs.modal', function () {
        $('.popover').popover('hide');
    });

    $('#event').on('keydown paste', function fbIdChange() {
        //create trigger to FB field change
        if(this.fbChange) clearTimeout(this.fbChange);
        this.fbChange = setTimeout(function() {
            $('#event').trigger('changeEnd');
        }, 1000);
    }).on('changeEnd', function() {
        // request from backend
        $.ajax({
            url: '/api/fb/event-title?event=' + encodeURIComponent(document.getElementById('event').value),
            dataType: "json",
            async: true,
            success: function (data) {
                $('#title-event').removeClass('hidden-xs-up');
                $('#error-event').addClass('hidden-xs-up');
                $('#text-event-name').text(data.title);
                $('#submit-import').removeClass('disabled');
            },
            error: function (data) {
                $('#title-event').addClass('hidden-xs-up');
                $('#error-event').removeClass('hidden-xs-up');
                $('#submit-import').addClass('disabled');
                console.log(data.responseJSON.error);
            }
        });
    });
</script>