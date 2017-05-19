<div class="tab-pane" id="tab-entries" role="tabpanel">
    <div class="row">
        <div class="col-xs-12">
            {{--Entry types--}}
            <div class="bracket">
                <h5>
                    <i class="fa fa-list-ol" aria-hidden="true"></i>
                    Entry types
                </h5>
                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <table class="table table-sm table-striped abr-table">
                            <thead>
                            <tr>
                                <th>type</th>
                                <th>number of entries</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($entry_types as $type => $count)
                                <tr>
                                    <td>{{ $type }}</td>
                                    <td>{{ $count }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="col-xs-12 col-md-6" id="chart-entry-types">
                    </div>
                </div>
            </div>
            {{--Decks--}}
            <div class="bracket">
                <h5>
                    <i class="fa fa-id-card-o" aria-hidden="true"></i>
                    Decks
                </h5>
                <p>
                    Total number of decks: {{ $published_count + $private_count }}
                </p>
                <div class="row">
                    <div class="col-md-6 col-xs-12">
                        Published decks: {{ $published_count }}<br/>
                        Private decks: {{ $private_count }}<br/>
                        Broken deck links: {{ $broken_count }} - users:
                        <?php $bcount = count($broken_users) ?>
                        @foreach($broken_users as $key=>$buser)
                            <a href="/profile/{{ $buser->id }}">{{ $buser->displayUsername() }}</a>{{ $key != $bcount-1 ? ',' : ''}}
                        @endforeach
                        <br/>
                        @if (Auth::user() && Auth::user()->id == 1276)
                            <a href="/admin/decks/broken" class="btn btn-primary">Detect broken</a>
                        @endif
                    </div>
                    <div class="col-md-6 col-xs-12">
                        With backlink to NetrunnerDB: {{ $backlink_count }}<br/>
                        Without backlink to NetrunnerDB: {{ $no_backlink_count }}<br/>
                        Unexported: {{ $unexported_count }}
                    </div>
                </div>
            </div>
            {{--KTM update--}}
            <div class="bracket">
                <h5>
                    <i class="fa fa-hourglass-half" aria-hidden="true"></i>
                    Know the Meta update
                </h5>
                <p>
                    Last update: {{ $ktm_update }}<br/>
                    New entries since:
                <ul>
                    @foreach($ktm_packs as $key=>$pack)
                        <li>{{$key}}: {{$pack[0]}} entries ({{$pack[1]}} claims)</li>
                    @endforeach
                </ul>
                </p>
            </div>
        </div>
    </div>
</div>