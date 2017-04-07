{{--Photos--}}
<h5>
    <i class="fa fa-camera" aria-hidden="true"></i>
    Photos
    @if (count($tournament->photos))
        <span class="user-counts">({{ count($tournament->photos) }})</span>
    @endif
    @if ($user)
        <button class="btn btn-primary btn-xs pull-right" id="button-add-photos"
                onclick="togglePhotoAdd(true)">
            <i class="fa fa-camera" aria-hidden="true"></i> Add photos
        </button>
        <button class="btn btn-primary btn-xs hidden-xs-up pull-right" id="button-done-photos"
                onclick="togglePhotoAdd(false)">
            <i class="fa fa-check" aria-hidden="true"></i> Done
        </button>
    @endif
</h5>
{{--Add photos--}}
<div id="section-add-photos" class="hidden-xs-up text-xs-center card-darker m-t-1 p-b-1">
    <hr/>
    <div class="p-b-1">
        Add photo
    </div>
    {!! Form::open(['method' => 'POST', 'url' => "/photos", 'files' => true, 'class' => 'form-inline']) !!}
    {!! Form::hidden('tournament_id', $tournament->id) !!}
    <div class="form-group">
        {!! Form::label('photo', 'photo', ['class' => 'small-text']) !!}
        <input id="photo" class="form-control" name="photo" type="file" required>
    </div><br/>
    <div class="form-group">
        {!! Form::label('title', 'title', ['class' => 'small-text']) !!}
        {!! Form::text('title', '', ['class' => 'form-control']) !!}
    </div>
    <br/>
    {!! Form::button('Add photo', array('type' => 'submit',
        'class' => 'btn btn-success btn-xs', 'id' => 'button-add-photo')) !!}
    {!! Form::close() !!}
    <span class="legal-bullshit">max 8MB png or jpg file</span>
</div>
<hr/>
{{--List of photos--}}
@if (count($tournament->photos))
    @include('tournaments.viewer.photolist')
@else
    <p><em id="no-photos">no photos yet</em></p>
@endif