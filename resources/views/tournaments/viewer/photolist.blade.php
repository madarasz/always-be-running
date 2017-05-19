<div class="row">
    @foreach($tournament->photos as $key => $photo)
        <div class="gallery-item col-xs-4{{ $key > 2 ? ' hidden-xs-up more-photos' : '' }}">
            <div style="position: relative;">
                <a href="{{ $photo->url() }}" data-toggle="lightbox" data-gallery="gallery" data-title="{{ $photo->title }}"
                   data-footer="{{ 'uploaded by <a href="/profile/'.$photo->user->id.'">'.$photo->user->displayUsername().'</a>' }}">
                    <img src="{{ $photo->urlThumb() }}"/>
                </a>
                <div class="abs-top-left">
                    @if (!$photo->approved && $user && $user->admin)
                        <a class="btn btn-sm btn-success fade-in" href="{{ '/photos/'.$photo->id.'/approve' }}">
                            <i class="fa fa-thumbs-up" aria-hidden="true"></i>
                        </a>
                    @endif
                    @if ($user && ($user->admin || $user->id == $photo->user_id || $user->id == $photo->tournament->creator))
                        {!! Form::open(['method' => 'DELETE', 'url' => "/photos/{$photo->id}", 'style' => 'display:inline;']) !!}
                            {!! Form::button('<i class="fa fa-trash" aria-hidden="true"></i>',
                            array('type' => 'submit', 'class' => 'btn btn-danger btn-sm fade-in', 'onclick' => 'return confirm("Delete photo?")')) !!}
                        {!! Form::close() !!}
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@if(count($tournament->photos) > 3)
    <div class="text-xs-center">
        <button class="btn btn-primary btn-xs" id="showPhotoList"
                onClick="$('.more-photos').removeClass('hidden-xs-up'); $('#showPhotoList').addClass('hidden-xs-up'); $('#hidePhotoList').removeClass('hidden-xs-up');" >
            <i class="fa fa-eye" aria-hidden="true"></i> Show All Photos
        </button>
        <button class="btn btn-primary btn-xs hidden-xs-up" id="hidePhotoList"
                onClick="$('.more-photos').addClass('hidden-xs-up'); $('#showPhotoList').removeClass('hidden-xs-up'); $('#hidePhotoList').addClass('hidden-xs-up');" >
            <i class="fa fa-eye-slash" aria-hidden="true"></i> Hide Photos
        </button>
    </div>
@endif
{{--Enable gallery--}}
<script type="application/javascript">
    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
        event.preventDefault();
        $(this).ekkoLightbox({alwaysShowClose: true});
    });
</script>