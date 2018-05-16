{{--Modal for new/edit prize kit--}}
<div class="modal fade" id="modal-prize" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">@{{ modalTitle }}</h4>
            </div>
            <div class="modal-body">

                <form method="POST" enctype="multipart/form-data" @submit.prevent="(editMode ? updatePrize() : createPrize())">

                    {{--Year--}}
                    <div class="form-group row">
                        <label for="year" class="col-sm-3 col-form-label">Year:</label>
                        <div class="col-sm-9">
                            <input type="number" name="year" class="form-control" v-model="prize.year" required />
                        </div>
                    </div>

                    {{--Title--}}
                    <div class="form-group row">
                        <label for="title" class="col-sm-3 col-form-label">Title:</label>
                        <div class="col-sm-9">
                            <input type="text" name="title" class="form-control" v-model="prize.title" required />
                        </div>
                    </div>

                    {{--Type--}}
                    <div class="form-group row">
                        <label for="tournament_type_id" class="col-sm-3 col-form-label">Type:</label>
                        <div class="col-sm-9">
                            <select name="tournament_type_id" class="form-control" id="location"
                                    v-model="prize.tournament_type_id" required>
                                @foreach($tournament_types as $key => $type)
                                    <option value="{{ $key }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{--Description--}}
                    <div class="form-group m-b-3">
                        <label for="description">Description:</label>
                        <textarea name="description" class="form-control" v-model="prize.description" rows="6"></textarea>
                        <div class="pull-right">
                            <small><a href="http://commonmark.org/help/" target="_blank" rel="nofollow"><img src="/img/markdown_icon.png"/></a> formatting is supported</small>
                            @include('partials.popover', ['direction' => 'top', 'content' =>
                                    '<a href="http://commonmark.org/help/" target="_blank">Markdown cheat sheet</a>'])
                        </div>
                    </div>

                    {{--FFG URL--}}
                    <div class="form-group row">
                        <label for="ffg_url" class="col-sm-3 col-form-label">FFG article URL:</label>
                        <div class="col-sm-9">
                            <input type="text" name="ffg_url" class="form-control" v-model="prize.ffg_url" />
                        </div>
                    </div>

                    {{--Order--}}
                    <div class="form-group row">
                        <label for="order" class="col-sm-3 col-form-label">
                            Ordering number:@include('partials.popover', ['direction' => 'bottom', 'content' =>
                                    'Prize kits are ordered by this number descending.'])
                        </label>
                        <div class="col-sm-9">
                            <input type="number" name="order" class="form-control" v-model="prize.order"/>
                        </div>
                    </div>

                    <div class="form-group text-xs-center m-t-1">
                        <button type="submit" class="btn btn-success">@{{ modalButton }}</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>