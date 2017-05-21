{{--Page header--}}
<h4 class="page-header">
    @if ($user && ($user->admin || $user->id == $tournament->creator))
        <div class="pull-right" id="control-buttons">
            {{--Feature - only for Necro --}}
            @if ($user && ($user->id == 1276))
            <a href="/tournaments/{{ $tournament->id }}/toggle-featured" class="btn btn-info" id="feature-button">
                <i class="fa {{ $tournament->featured ? 'fa-star-half-o' :'fa-star' }}" aria-hidden="true"></i>
            </a>
            @endif
            {{--Edit--}}
            <a href="{{ "/tournaments/$tournament->id/edit" }}" class="btn btn-primary" id="edit-button"><i class="fa fa-pencil" aria-hidden="true"></i> Update</a>
            {{--Transfer--}}
            <button class="btn btn-primary" data-toggle="modal" data-hide-manual="true"
                    data-target="#transferModal" id="button-transfer">
                <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i> Transfer
            </button>
            {{--Approval --}}
            @if ($user && $user->admin)
                @if ($tournament->approved !== "1")
                    <a href="/tournaments/{{ $tournament->id }}/approve" class="btn btn-success" id="approve-button"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Approve</a>
                @endif
                @if ($tournament->approved !== "0")
                    <a href="/tournaments/{{ $tournament->id }}/reject" class="btn btn-danger" id="reject-button"><i class="fa fa-thumbs-down" aria-hidden="true"></i> Reject</a>
                @endif
            @endif
            {{--Delete--}}
            @if (is_null($tournament->deleted_at))
                {!! Form::open(['method' => 'DELETE', 'url' => "/tournaments/$tournament->id", 'class' => 'inline-block']) !!}
                {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i> Delete tournament', array('type' => 'submit', 'class' => 'btn btn-danger', 'id' => 'delete-button')) !!}
                {!! Form::close() !!}
                {{--Restore--}}
            @elseif ($user->admin)
                <a href="/tournaments/{{ $tournament->id }}/restore" class="btn btn-primary" id="restore-button"><i class="fa fa-recycle" aria-hidden="true"></i> Restore</a>
            @endif
        </div>
    @endif
    <span id="tournament-title">{{ $tournament->title }}</span><br/>
    <small>
        <span id="tournament-type">{{ $type }}</span> -
        <em>
            created by
            {{--Patreon sysop goal--}}
            @if ($tournament->user->supporter > 2)
                <img class="img-patron-o" title="Patreon Sysop/Executive supporter"/>
            @endif
            <span id="tournament-creator">
                <a href="/profile/{{ $tournament->user->id }}" {{ $tournament->user->supporter ? 'class=supporter' : '' }}>{{ $tournament->user->displayUsername() }}</a>
            </span>
        </em>
        {{--Charity--}}
        @if ($tournament->charity)
            -
            <i title="charity" class="fa fa-heart text-danger"></i>
            charity event
        @endif
        @if ($tournament->incomplete)
            <div class="alert alert-danger view-indicator" id="viewing-as-admin">
                <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                This is tournament is incomplete. Please UPDATE and fill out missing fields.
            </div>
        @endif
        @if ($tournament->deleted_at)
            <div class="alert alert-danger view-indicator" id="viewing-as-admin">
                <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                This is a deleted tournament. Admins can restore it.
            </div>
        @endif
        @if ($user && $user->admin)
            <div class="alert alert-success view-indicator" id="viewing-as-admin">
                <i class="fa fa-info-circle" aria-hidden="true"></i>
                viewing as admin
            </div>
        @elseif ($user && $user->id == $tournament->creator)
            <div class="alert alert-success view-indicator" id="viewing-as-creator">
                <i class="fa fa-info-circle" aria-hidden="true"></i>
                viewing as creator
            </div>
        @endif
    </small>
</h4>