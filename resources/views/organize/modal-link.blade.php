{{--Modal for linking tournaments groups--}}
<div class="modal fade" id="modal-link-group" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">Link Tournament</h4>
            </div>
            <div class="modal-body">

                <form>

                    <div class="card card-darker">
                        <div class="card-block">
                            <h5>
                                <i class="fa fa-filter" aria-hidden="true"></i>
                                Filter
                            </h5>
                            <div class="form-group row">
                                <label for="title" class="col-sm-3 col-form-label">Country:</label>
                                <div class="col-sm-9">
                                    <select name="location" class="form-control" id="location"
                                            v-model="linkForm.location" @change="loadTournaments()">
                                        <option value="all">- all countries -</option>
                                        <option value="online">- online -</option>
                                        @foreach($usedCountries as $key => $country)
                                                <option value="{{ $key }}">{{ $country }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row p-t-1">
                                <div class="form-check form-check-inline text-xs-center" style="width: 100%">
                                    <input class="form-check-input" type="checkbox" value="" v-model="linkForm.own"
                                           @change="loadTournaments()" style="margin-left:0">
                                    <label class="form-check-label" for="defaultCheck1">
                                        only tournaments created by me
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{--Tournament--}}
                    <div class="form-group row">
                        <label for="game_type_id" class="col-sm-3 col-form-label">Tournament:</label>
                        <div class="col-sm-9">
                            <select name="location" class="form-control" id="location" v-model="linkForm.tournament" required>
                                <option v-for="tournament in linkForm.tournaments" :value="tournament.id">
                                    @{{ tournament.date }} - @{{ tournament.title }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group text-xs-center m-t-1">
                        <a class="btn btn-primary white-text" @click.stop="linkTournament()">Link</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>