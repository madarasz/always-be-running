{{--Personal page-Photos tab content--}}
<div class="row">
    <div class="col-xs-12">
        <div class="bracket">
            <h5 class="p-b-2">
                <i class="fa fa-camera" aria-hidden="true"></i>
                My photos ({{ $photo_count }})<br/>
                <small>photos I have uploaded</small>
            </h5>
            @foreach($photo_tournaments as $tournament)
                <h6>
                    <a href="{{ $tournament->seoURL() }}">{{ $tournament->title }}</a>
                    <small>
                        - {{ $tournament->location() }} - {{ $tournament->date }}
                    </small>
                </h6>
                <hr/>
                <div class="row">
                    @foreach($tournament->photos as $photo)
                        <div class="gallery-item col-xs-6 col-md-4 col-lg-3">
                            @include('partials.gallery-item', ['button_rotate' => true, 'button_delete' => true])
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</div>