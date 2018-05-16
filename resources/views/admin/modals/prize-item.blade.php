{{--Modal for new/edit prize item--}}
<div class="modal fade" id="modal-prize-item" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">@{{ modalTitle }}</h4>
            </div>
            <div class="modal-body">

                <form method="POST" enctype="multipart/form-data" @submit.prevent="(editMode ? updateItem() : createItem())">

                    {{--Prize ID--}}
                    <input type="hidden" name="prize_id" v-model="item.prize_id"/>

                    {{--Quantity--}}
                    <div class="form-group row">
                        <label for="quantity" class="col-sm-3 col-form-label">
                            Quantity:
                            @include('partials.popover', ['direction' => 'top', 'content' =>
                                    'Do not count copies for TO.<br/>Leave empty for participation prize (every player gets one).'])
                        </label>
                        <div class="col-sm-9">
                            <input type="text" name="quantity" class="form-control" v-model="item.quantity"
                                   placeholder="leave empty for participation prize"/>
                        </div>
                    </div>

                    {{--Title--}}
                    <div class="form-group row">
                        <label for="title" class="col-sm-3 col-form-label">Title:</label>
                        <div class="col-sm-9">
                            <input type="text" name="title" class="form-control" v-model="item.title"  />
                        </div>
                    </div>

                    {{--Type--}}
                    <div class="form-group row">
                        <label for="tournament_type_id" class="col-sm-3 col-form-label">Type:</label>
                        <div class="col-sm-9">
                            <select name="type-helper" class="form-control" v-model="item.typeHelper"
                                    @change="item.type = item.typeHelper.trim()" required>
                                <option value=" ">--- other ---</option>
                                <option :value="itemType" v-for="itemType in itemTypes">@{{ itemType }}</option>
                            </select>
                            <input :type="item.typeHelper == ' ' ? 'text' : 'hidden'" name="type" class="form-control"
                                   v-model="item.type" placeholder="enter type" required/>
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