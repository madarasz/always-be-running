<div class="row">
    @foreach($photos as $photo)
        @if ($photo->approved == $approval)
            <div class="gallery-item col-xs-3">
                @include('partials.gallery-item',
                ['button_approve' => is_null($approval),'button_rotate' => true, 'button_tournament' => true, 'button_delete' => true])
            </div>
        @endif
    @endforeach
</div>