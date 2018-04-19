{{--Tournament Group membership--}}
<div class="bracket" id="{{ 'bracket-group-'.$group->id }}">
    <h5>
        @if($user->id == $group->creator || $user->admin)
            <a href="/organize#tab-groups" class="btn btn-xs btn-primary pull-right">
                <i class="fa fa-pencil" aria-hidden="true"></i>
                edit
            </a>
        @endif
        <i class="fa fa-folder-open" aria-hidden="true"></i>
        {{ $group->title }}
    </h5>
    <strong>location:</strong> {{ $group->location }}
    <table class="table table-sm table-striped abr-table table-doublerow m-t-1">
        <thead>
            <th>date</th>
            <th>tournament</th>
        </thead>
        <tbody>
            @foreach($group->tournaments as $gtournament)
                @if ($gtournament->id == $tournament->id)
                    <tr class="row-selected">
                        <td><strong>{{ $gtournament->date }}</strong></td>
                        <td><strong>{{ $gtournament->title }}</strong></td>
                    </tr>
                @else
                    <tr>
                        <td>{{ $gtournament->date }}</td>
                        <td>
                            <a href="{{ $gtournament->seoUrl }}">
                                {{ $gtournament->title }}
                            </a>
                        </td>
                    </tr>
                @endif

            @endforeach
        </tbody>
    </table>
</div>