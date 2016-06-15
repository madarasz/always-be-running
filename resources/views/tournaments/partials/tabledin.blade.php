<h5>
    @unless (empty($icon))
        <i class="fa {{ $icon }}" aria-hidden="true"></i>
    @endunless
    {{ $title }}
</h5>
<table class="table table-sm table-striped" id="{{ $id }}">
    <thead>
        @if( in_array('title', $columns) )
            <th>title</th>
        @endif
        @if( in_array('date', $columns) )
            <th>date</th>
        @endif
        @if( in_array('location', $columns) )
            <th>location</th>
        @endif
        @if( in_array('cardpool', $columns) )
            <th>cardpool</th>
        @endif
        @if( in_array('type', $columns) )
            <th>type</th>
        @endif
        @if( in_array('approval', $columns) )
            <th class="text-xs-center">approval</th>
        @endif
        @if( in_array('user_claim', $columns) )
            <th class="text-xs-center">claim</th>
        @endif
        @if( in_array('conclusion', $columns) )
            <th class="text-xs-center">conclusion</th>
        @endif
        @if( in_array('players', $columns) )
            <th class="text-xs-center">players</th>
        @endif
        @if( in_array('claims', $columns) )
            <th class="text-xs-center">claims</th>
        @endif
        @if( in_array('action_view', $columns) )
            <th></th>
        @endif
        @if( in_array('action_edit', $columns) )
            <th></th>
        @endif
        @if( in_array('action_approve', $columns) )
            <th></th>
        @endif
        @if( in_array('action_reject', $columns) )
            <th></th>
        @endif
        @if( in_array('action_delete', $columns) )
            <th></th>
        @endif
        @if( in_array('action_restore', $columns) )
            <th></th>
        @endif
    </thead>
    <tbody>
    </tbody>
</table>