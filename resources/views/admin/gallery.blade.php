<div class="row">
    @foreach($photos as $photo)
        @if ($photo->approved == $approval)
            <div class="gallery-item col-xs-3">
                <div style="position: relative;">
                    <a href="{{ $photo->url() }}" data-toggle="lightbox" data-gallery="gallery" data-title="{{ $photo->title }}"
                       data-footer="{{ 'uploaded by <a href="/profile/'.$photo->user->id.'">'.$photo->user->displayUsername().'</a>' }}">
                        <img src="{{ $photo->urlThumb() }}"/>
                    </a>
                    <div class="abs-top-left">
                        {{--approve button--}}
                        @if (is_null($approval))
                            <a class="btn btn-sm btn-success fade-in" href="{{ '/photos/'.$photo->id.'/approve' }}">
                                <i class="fa fa-thumbs-up" aria-hidden="true"></i>
                            </a>
                        @endif
                        {{--rotate button--}}
                        <a class="btn btn-sm btn-primary fade-in" href="{{ '/photos/'.$photo->id.'/rotate/ccw' }}">
                            <i class="fa fa-undo" title="rotate"></i>
                        </a>
                        <a class="btn btn-sm btn-primary fade-in" href="{{ '/photos/'.$photo->id.'/rotate/cw' }}">
                            <i class="fa fa-repeat" title="rotate"></i>
                        </a>
                        {{--tournament button--}}
                        <a href="{{ '/tournaments/'.@$photo->tournament->id }}" class="btn btn-sm btn-info fade-in">
                            <i class="fa fa-list-alt" aria-hidden="true"></i>
                        </a>
                        {{--delete button--}}
                        {!! Form::open(['method' => 'DELETE', 'url' => "/photos/{$photo->id}", 'style' => 'display:inline;']) !!}
                            {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i>',
                                array('type' => 'submit', 'class' => 'btn btn-danger btn-sm fade-in', 'onclick' => 'return confirm("Delete photo?")')) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>