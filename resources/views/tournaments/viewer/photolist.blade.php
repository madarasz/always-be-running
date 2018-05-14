<div class="row">
    @foreach($tournament->photos as $key => $photo)
        <div class="gallery-item col-xs-4{{ $key > 2 ? ' hidden-xs-up more-photos' : '' }}">
            @include('partials.gallery-item',
                ['button_approve' => !$photo->approved && $user && $user->admin,
                'button_rotate' => ($user && ($user->admin || $user->id == $photo->user_id || $user->id == $photo->tournament->creator)),
                'button_delete' => ($user && ($user->admin || $user->id == $photo->user_id || $user->id == $photo->tournament->creator))])
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