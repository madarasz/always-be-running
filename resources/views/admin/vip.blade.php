<div class="tab-pane" id="tab-vip" role="tabpanel">
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
                <h5>
                    Most active users
                </h5>
                <table class="table table-sm table-striped abr-table" id="tags">
                    <thead>
                    <tr>
                        <th>user</th>
                        <th>#claims</th>
                        <th>#TO</th>
                        <th>#claimers</th>
                        <th>reputation</th>
                        <th>#badges</th>
                        <th>country</th>
                        <th>email</th>
                        <th>last login</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($vips->sortBy(function($q) {return $q->claims->count()+$q->tournamentsCreated()->count();}, SORT_REGULAR, true) as $vip)
                            @if ($vip->claims()->count() >= 5 || $vip->tournamentsCreated()->count() >=5)
                                @include('admin.vip-row')
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="bracket">
                <h5>
                    Users with most reputation
                </h5>
                <table class="table table-sm table-striped abr-table" id="tags">
                    <thead>
                    <tr>
                        <th>user</th>
                        <th>#claims</th>
                        <th>#TO</th>
                        <th>#claimers</th>
                        <th>reputation</th>
                        <th>#badges</th>
                        <th>country</th>
                        <th>email</th>
                        <th>last login</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($vips->sortBy(function($q) {return $q->reputation;}, SORT_REGULAR, true) as $vip)
                        @if ($vip->reputation >= 1000)
                            @include('admin.vip-row')
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>