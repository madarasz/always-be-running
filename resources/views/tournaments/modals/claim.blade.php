{{--Claim tournament spot modal--}}
<div class="modal fade" id="claimModal" tabindex="-1" role="dialog" aria-labelledby="claim modal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    Claim spot on tournament<br/>
                    <div class="modal-subtitle" id="modal-subtitle"></div>
                </h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid bd-example-row">
                    {!! Form::open(['url' => "", 'id' => 'create-claim']) !!}
                        {!! Form::hidden('top_number', '') !!}
                        <div class="row">
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                    {!! Form::label('rank', 'rank after swiss rounds') !!}
                                    {!! Form::select('rank', [], null, ['class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-6" id="claim-top-section">
                                <div class="form-group">
                                    {!! Form::label('rank_top', 'rank after top cut') !!}
                                    {!! Form::select('rank_top', [], null, ['class' => 'form-control']) !!}
                                </div>
                            </div>
                        </div>
                        {{--Dropdown selectors for decks--}}
                        <div class="row">
                            <div class="col-xs-12 col-md-6">
                                <div class="deck-loader">loading</div>
                                <div class="form-group">
                                    {!! Form::label('corp_deck', 'corporation deck') !!}
                                    {!! Form::select('corp_deck', [], null, ['class' => 'form-control']) !!}
                                    <div class="alert alert-danger hidden-xs-up" id="no-corp-deck">
                                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                        You don't have any published decklist on NetrunnerDB.
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-6">
                                <div class="deck-loader">loading</div>
                                <div class="form-group">
                                    {!! Form::label('runner_deck', 'runner deck') !!}
                                    {!! Form::select('runner_deck', [], null, ['class' => 'form-control']) !!}
                                    <div class="alert alert-danger hidden-xs-up" id="no-runner-deck">
                                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                        You don't have any published decklist on NetrunnerDB.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-xs-center">
                            <button type="submit" class="btn btn-claim disabled" id="submit-claim">
                                Claim spot
                            </button>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
{{--Script to fill tournament claim modal--}}
<script type="text/javascript">
    var deckData = null, loading = false;

    $('#claimModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var subtitle = button.data('subtitle'), // module subtitle
                id = button.data('tournament-id'),  // tournament ID
                players_number = button.data('players-number'),
                top_number = button.data('top-number');
        var modal = $(this);
        modal.find('.modal-subtitle').text(subtitle);
        modal.find('#create-claim').attr("action", "/tournaments/" + id + "/claim");
        modal.find('#top_number').val(top_number);

        // ranks
        modal.find('#rank').empty();
        for (var count = 1; count <= players_number; count++) {
            modal.find('#rank').append($('<option>', {value: count, text: count}));
        }

        // top rank
        if (top_number) {
            modal.find('#claim-top-section').removeClass('hidden-xs-up');
            modal.find('#rank_top').empty();
            modal.find('#rank_top').append($('<option>', {value: '0', text: 'below top cut'}));
            for (count = 1; count <= top_number; count++) {
                modal.find('#rank_top').append($('<option>', {value: count, text: count}));
            }
        } else {
            modal.find('#claim-top-section').addClass('hidden-xs-up');
        }

        // load deck via API
        if (!deckData) {
            loadDecks();
        }

        function loadDecks() {
            if (!loading) { // don't start loading multiple times
                modal.find('.deck-loader').addClass('loader').removeClass('hidden-xs-up');
                loading = true;
                $.ajax({
                    url: '/api/userdecks',
                    dataType: "json",
                    async: true,
                    success: function (data) {
                        displayListOfDecksForClaims('runner', data);
                        displayListOfDecksForClaims('corp', data);
                        deckData = data;
                        modal.find('.deck-loader').removeClass('loader').addClass('hidden-xs-up');
                        modal.find('#submit-claim').removeClass('disabled');
                        loading = false;
                        // todo: login if needed
                    }
                });
            }
        }

        // populates select element for tournament claim form deck selector
        function displayListOfDecksForClaims(side, data) {
            var rootElement = '#'+side+'_deck',
                    publicRoot = modal.find(rootElement),
                    privateRoot = modal.find(rootElement);

            modal.find(rootElement).empty();

            // add optgroups
            if (data.privateNetrunnerDB[side].length && data.publicNetrunnerDB[side].length) {
                modal.find(rootElement).append($('<optgroup>', { label: '--- published decks ---', id: side+'_public'}));
                modal.find(rootElement).append($('<optgroup>', { label: '--- private decks ---', id: side+'_private'}));
                publicRoot = modal.find('#'+side+'_public');
                privateRoot = modal.find('#'+side+'_private');
            }

            // add public decks
            if (data.publicNetrunnerDB) {
                displayDecksForClaims(data.publicNetrunnerDB[side], publicRoot, 1);
            }
            // add private decks
            if (data.privateNetrunnerDB) {
                displayDecksForClaims(data.privateNetrunnerDB[side], privateRoot, 2);
            }
        }

        // populates option lines for tournament claim form deck selector
        function displayDecksForClaims(data, rootElement, type) {
            // todo: ordering
            $.each(data, function (index, element) {
                rootElement.append($('<option>', {
                    value: "{ \"title\": \"" + element.name.replace(/'/g, "\\'") +
                    "\", \"id\": \"" + element.id + "\", \"identity\": \"" + element.identity +
                    "\", \"type\": \"" + type + "\" }",
                    text: element.name
                }));
            });
        }
    });


        // todo: show no deck error

</script>