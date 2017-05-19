<div class="tab-pane" id="tab-packs" role="tabpanel">
    <div class="row">
        <div class="col-md-6 col-xs-12">
            <div class="bracket">
                <h5>Cardpool usage</h5>
                <ul>
                    @for($i = 0; $i < count ($cycles); $i++)
                        <li>{{ $cycles[$i]->name }}</li>
                        <ul>
                            @foreach ($packs[$i] as $pack)
                                <li>
                                    {{ $pack->name }}
                                    @if ($pack->usable)
                                        <a href="{{ "/packs/$pack->id/disable" }}" class="btn-danger btn btn-xs">disable</a>
                                    @else
                                        <a href="{{ "/packs/$pack->id/enable" }}" class="btn-success btn btn-xs">enable</a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endfor
                </ul>
            </div>
        </div>
        <div class="col-md-6 col-xs-12">
            <div class="bracket">
                <h5>Card data</h5>
                <a href="/admin/cycles/update" class="btn-primary btn btn-sm">Update Card cycles</a> Card cycle count: {{ $count_cycles }} (last: <em>{{ $last_cycle }}</em>)<br/>
                <a href="/admin/packs/update" class="btn-primary btn btn-sm">Update Card packs</a> Card pack count: {{ $count_packs }} (last: <em>{{ $last_pack }}</em>)<br/>
                <a href="/admin/identities/update" class="btn-primary btn btn-sm">Update Identities</a> Identity count: {{ $count_ids }} (last: <em>{{ $last_id }}</em>)
            </div>
        </div>
    </div>
</div>