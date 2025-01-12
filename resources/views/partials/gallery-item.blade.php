<div style="position: relative;">
    {{--image thumpnail--}}
    <a href="{{ $photo->url() }}" data-toggle="lightbox" data-gallery="gallery" data-title="{{ $photo->title }}"
    data-footer="{{ 'uploaded by <a href="/profile/'.(($photo->user) ? $photo->user->id : '').'">'.(($photo->user) ? $photo->user->displayUsername() : 'Unknown User').'</a>' }}">
        <img src="{{ $photo->urlThumb() }}"/>
    </a>

    {{--buttons--}}
    <div class="abs-top-left">
        {{--approval button--}}
        @if (@$button_approve)
            <a class="btn btn-sm btn-success fade-in" href="{{ '/photos/'.$photo->id.'/approve' }}">
                <i class="fa fa-thumbs-up" aria-hidden="true"></i>
            </a>
        @endif
        {{--rotate buttons--}}
        @if (@$button_rotate)
            <a class="btn btn-sm btn-primary fade-in" href="{{ '/photos/'.$photo->id.'/rotate/ccw' }}">
                <i class="fa fa-undo" title="rotate"></i>
            </a>
            <a class="btn btn-sm btn-primary fade-in" href="{{ '/photos/'.$photo->id.'/rotate/cw' }}">
                <i class="fa fa-repeat" title="rotate"></i>
            </a>
        @endif
        {{--tournament button--}}
        @if (@$button_tournament && $photo->tournament && @$photo->tournament->id)
            <a href="{{ '/tournaments/'.@$photo->tournament->id }}" class="btn btn-sm btn-info fade-in">
                <i class="fa fa-list-alt" aria-hidden="true"></i>
            </a>
        @endif
        {{--delete button--}}
        @if (@$button_delete)
            {!! Form::open(['method' => 'DELETE', 'url' => "/photos/{$photo->id}", 'style' => 'display:inline;']) !!}
            {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i>',
                array('type' => 'submit', 'class' => 'btn btn-danger btn-sm fade-in', 'onclick' => 'return confirm("Delete photo?")')) !!}
            {!! Form::close() !!}
        @endif
    </div>
</div>