<div class="tab-pane active" id="tab-tournaments" role="tabpanel">
    {{--Notification for approve--}}
    <div class="alert alert-warning view-indicator hidden-xs-up" id="notif-tournament">
        <i class="fa fa-clock-o" aria-hidden="true"></i>
        You have tournaments waiting for approval or having conflicts.
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
                {{--Pending--}}
                @include('tournaments.partials.tabledin',
                    ['columns' => ['title', 'date', 'location', 'cardpool', 'approval', 'players',
                        'action_edit', 'action_approve', 'action_reject', 'action_delete'],
                    'title' => 'Pending tournaments', 'id' => 'pending', 'icon' => 'fa-question-circle-o', 'loader' => true])
                @include('tournaments.partials.tabledin',
                    ['columns' => ['title', 'date', 'location', 'cardpool', 'approval', 'players',
                        'action_edit', 'action_approve', 'action_delete'],
                    'title' => 'Rejected tournaments', 'id' => 'rejected', 'icon' => 'fa-thumbs-down', 'loader' => true])
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
                {{--Conflict--}}
                @include('tournaments.partials.tabledin',
                    ['columns' => ['title', 'date', 'location', 'creator', 'approval', 'players', 'claims', 'action_delete'],
                    'title' => 'Conflicts',
                    'id' => 'conflict', 'icon' => 'fa-exclamation-triangle', 'loader' => true])
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
                {{--Deleted--}}
                @include('tournaments.partials.tabledin',
                    ['columns' => ['title', 'date', 'creator', 'approval', 'conclusion', 'players', 'decks',
                        'action_edit', 'action_restore', 'action_purge'],
                    'title' => 'Deleted tournaments',
                    'id' => 'deleted', 'icon' => 'fa-times-circle-o', 'loader' => true])
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
                @include('tournaments.partials.tabledin',
                    ['columns' => ['title', 'date', 'location', 'cardpool', 'creator', 'players',
                        'created_at', 'action_edit', 'action_purge' ],
                    'title' => 'Incomplete imports',
                    'id' => 'incomplete', 'icon' => 'fa-exclamation-triangle', 'loader' => true])
            </div>
        </div>
    </div>
</div>