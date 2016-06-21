@extends('layout.general')

@section('content')
    <h4 class="page-header">Personal</h4>
    @include('partials.message')
    <div class="row">
        <div class="col-md-4 col-xs-12">
            <div class="bracket">
                <h5>
                    <i class="fa fa-calendar" aria-hidden="true"></i>
                    My calendar<br/>
                    <small>tournaments I registered to</small>
                </h5>
            </div>
        </div>
        <div class="col-md-8 col-xs-12">
            <div class="bracket">
                @include('tournaments.partials.tabledin',
                ['columns' => ['title', 'location', 'date', 'cardpool', 'user_claim'],
                'title' => 'My tournaments', 'subtitle' => 'tournaments I registered to',
                 'id' => 'my-table', 'icon' => 'fa-list-alt'])
            </div>
        </div>
    </div>
    <hr/>
    <div class="row">
        <div class="col-md-4 col-xs-12">
            <div class="bracket">
                <h5>
                    <i class="fa fa-wrench" aria-hidden="true"></i>
                    Personal settings
                </h5>
                <br/>
                <div class="text-xs-center">
                    <a href="" class="btn btn-primary">Update settings</a>
                </div>
            </div>
        </div>
        <div class="col-md-8 col-xs-12">
            <div class="bracket">
                <div class="pull-right">
                    <a href="" class="btn btn-primary">View public profile</a>
                </div>
                <h5>
                    <i class="fa fa-user" aria-hidden="true"></i>
                    Public profile information
                </h5>
                <br/>
                <strong>Usernames</strong>
                <form>
                    <div class="form-group row">
                        <label class="col-sm-offset-1 col-sm-3 form-control-label">NetrunnerDB</label>
                        <div class="col-sm-7">
                            <p class="form-control-static">Necro</p>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-offset-1 col-sm-3 form-control-label">Jinteki.net</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-offset-1 col-sm-3 form-control-label"></label>
                        <div class="col-sm-7">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" value="">
                                    use Jinteki.net avatar
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-offset-1 col-sm-3 form-control-label">OCTGN</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-offset-1 col-sm-3 form-control-label">Stimhack forum</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

