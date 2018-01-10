<div class="bracket">
    <h5>
        <i class="fa fa-list-alt" aria-hidden="true"></i>
        Created tournaments ({{$created_count}})
    </h5>
    <ul id="list-created">
        @foreach($created as $key=>$tournament)
            <li id="list-created-row-{{ $key+1 }}" class="{{ $key>=$maxrows ? 'hidden-xs-up':'' }}">
                @include('tournaments.partials.list-type', ['tournament' => $tournament, 'class' => 'no-li'])
                @include('tournaments.partials.list-format', ['tournament' => $tournament, 'class' => 'no-li'])
                <a href="{{ $tournament->seoUrl() }}">
                    {{ $tournament->title }}
                </a><br/>
                <div class="small-text">
                    {{ $tournament->tournament_type()->first()->type_name }} -
                    @if($tournament->tournament_type_id != 7)
                        {{ $tournament->location_country }}, {{$tournament->location_country === 'United States' ? $tournament->location_state.', ' : ''}}{{ $tournament->location_city }}
                    @else
                        online
                    @endif
                    @if ($tournament->date)
                        ({{ $tournament->date }})
                    @endif
                </div>
            </li>
        @endforeach
    </ul>
    @include('tournaments.partials.pager', ['id' => 'list-created', 'maxrows' => $maxrows])
</div>
