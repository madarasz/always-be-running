{{--Modal for new/edit tournament group--}}
<div class="modal fade" id="modal-group" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">@{{ modalTitle }}</h4>
            </div>
            <div class="modal-body">

                <form method="POST" enctype="multipart/form-data" @submit.prevent="(editMode ? updateGroup() : createGroup())">

                    {{--Title--}}
                    <div class="form-group row">
                        <label for="title" class="col-sm-3 col-form-label">Title:</label>
                        <div class="col-sm-9">
                            <input type="text" name="title" class="form-control" v-model="group.title" required />
                        </div>
                    </div>

                    {{--Description--}}
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea name="description" class="form-control" v-model="group.description"></textarea>
                    </div>

                    {{--Location--}}
                    <div class="form-group row">
                        <label for="game_type_id" class="col-sm-3 col-form-label">Location:</label>
                        <div class="col-sm-9">
                            <select name="location" class="form-control" id="location" v-model="group.location">
                                <optgroup label="non-country">
                                    <option value="online">- online -</option>
                                    <option value="online">- multiple countries -</option>
                                </optgroup>
                                <optgroup label="used countries">
                                    @foreach($usedCountries as $key => $country)
                                        <option value="{{ $key }}">{{ $country }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="all countries">
                                    @foreach($countries as $key => $country)
                                        <option value="{{ $key }}">{{ $country }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
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