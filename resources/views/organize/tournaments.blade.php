{{--Organize page - Tournaments tab--}}
{{--Notifications for conclude, unknown cardpool incomplete--}}
<div class="alert alert-warning view-indicator notif-red notif-badge-page hidden-xs-up" id="notif-conclude" data-badge="">
    <i class="fa fa-clock-o" aria-hidden="true"></i>
    You have tournaments waiting for conclusion.
</div>
<div class="alert alert-warning view-indicator notif-red notif-badge-page hidden-xs-up" id="notif-cardpool" data-badge="">
    <i class="fa fa-clock-o" aria-hidden="true"></i>
    It's time to set the cardpool for some of your tournaments.
</div>
<div class="alert alert-warning view-indicator notif-red notif-badge-page hidden-xs-up" id="notif-incomplete" data-badge="">
    <i class="fa fa-clock-o" aria-hidden="true"></i>
    You have incomplete imports. Please update or delete.
</div>

{{--Table for tournaments created by me--}}
<div class="row">
    <div class="col-xs-12">
        <div class="bracket">
            @include('tournaments.partials.tabledin',
                ['columns' => ['title', 'date', 'location', 'cardpool', 'approval', 'conclusion', 'players', 'claims',
                    'action_edit', 'action_delete' ], 'doublerow' => true,
                'title' => 'Tournaments created by me', 'id' => 'created', 'icon' => 'fa-list-alt', 'loader' => true])
        </div>
        <div class="bracket hidden-xs-up" id="bracket-incomplete">
            @include('tournaments.partials.tabledin',
                ['columns' => ['title', 'date', 'location', 'cardpool', 'players',
                    'created_at', 'action_edit', 'action_purge' ], 'doublerow' => true,
                'title' => 'Incomplete imports', 'subtitle' => 'please update or delete',
                'id' => 'incomplete', 'icon' => 'fa-exclamation-triangle'])
        </div>
    </div>
</div>