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
                    @include('partials.calendar')
                </h5>
            </div>
        </div>
        <div class="col-md-8 col-xs-12">
            <div class="bracket">
                @include('tournaments.partials.tabledin',
                ['columns' => ['title', 'location', 'date', 'cardpool', 'user_claim'],
                'title' => 'My tournaments', 'subtitle' => 'tournaments I registered to',
                 'id' => 'my-table', 'icon' => 'fa-list-alt', 'loader' => true])
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
                <form class="m-t-2">
                    <div class="form-group">
                        <label for="country">Country</label>
                        <select class="form-control" id="country">
                            <option>---</option>
                        </select>
                        <small class="text-muted">your default country filter</small>
                    </div>
                    <div class="form-group">
                        <label for="fday">First day of week</label>
                        <select class="form-control" id="fday">
                            <option>Monday</option>
                            <option>Sunday</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fdate">Date format</label>
                        <select class="form-control" id="fdate">
                            <option>YYYY.MM.DD.</option>
                            <option>DD/MM/YYY</option>
                            <option>MM-DD-YYYY</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Email notification</label><br/>
                        <input id="notif-conclude" type="checkbox">
                        <label for="notif-conclude">tournament due for conclusion<br/>
                            <small class="text-muted">for tournaments you created</small>
                        </label><br/>

                        <input id="notif-claim" type="checkbox">
                        <label for="notif-claim">tournament spot can be claimed<br/>
                            <small class="text-muted">for tournaments you registered to</small>
                        </label>
                    </div>
                </form>
                <div class="text-xs-center m-t-2">
                    <input type="button" value="Update settings" class="btn btn-primary disabled"/>
                    @include('partials.tobedeveloped')
                </div>
            </div>
        </div>
        <div class="col-md-8 col-xs-12">
            <div class="bracket">
                <div class="pull-right">
                    <input type="button" value="View public profile" class="btn btn-primary disabled"/>
                    @include('partials.tobedeveloped')
                </div>
                <h5>
                    <i class="fa fa-user" aria-hidden="true"></i>
                    Public profile information
                </h5>
                <br/>
                <form>
                    <strong>Usernames</strong>
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
                    <br/>
                    <strong>Other information</strong>
                    <div class="form-group row">
                        <label class="col-sm-offset-1 col-sm-3 form-control-label">Website/blog</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-offset-1 col-sm-3 form-control-label">About</label>
                        <div class="col-sm-7">
                            <textarea rows="3" cols="" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="text-xs-center m-t-2">
                        <input type="button" class="btn btn-primary disabled" value="Save profile info"/>
                        @include('partials.tobedeveloped')
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        getTournamentData('foruser={{ $user }}', function(data) {
            $('.loader').addClass('hidden-xs-up');
            updateTournamentTable('#my-table', ['title', 'location', 'date', 'cardpool', 'user_claim'], 'no tournaments to show', data);
            updateTournamentCalendar(data);
        });
    </script>
@stop

