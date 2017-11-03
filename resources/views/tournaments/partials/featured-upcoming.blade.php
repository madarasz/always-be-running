<div class="row">
    <div class="col-xs-12">
        <div class="bracket">
            <h5>
                <i class="fa fa-star" aria-hidden="true"></i>
                Featured
                @include('partials.popover', ['direction' => 'right', 'content' =>
                                    'Handpicked, criteria being considered:
                                    <ul>
                                        <li>big tournament</li>
                                        <li>happening soon</li>
                                        <li>unique event</li>
                                        <li>TO is a <a href="/support-me">supporter</a></li>
                                    </ul>'])
            </h5>
            <div class="row m-l-1 m-r-1" style="height: 5em; overflow: hidden">
                @foreach($featured as $ft)
                    <div class="col-md-4 col-xs-6">
                        <div class="featured-box">
                            <div class="featured-header">
                                <div class="featured-bg" style="background-image: url({{ $ft->coverImage() }});"></div>
                                <div class="stuff">
                                    <div class="featured-title">
                                        @if ($ft->charity)
                                            <i title="charity" class="fa fa-heart text-danger"></i>
                                        @endif
                                        @include('tournaments.partials.list-type', ['tournament' => $ft])
                                        @include('tournaments.partials.list-format', ['tournament' => $ft])
                                        <a href="{{ $ft->seoUrl() }}">
                                            <strong>{{ $ft->title }}</strong>
                                        </a>
                                    </div>
                                    <div class="featured-title">
                                        ({{ $ft->date }}) -
                                        {{ $ft->tournament_type_id == 7 ? 'online' : $ft->location_country }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                {{--support me box--}}
                <div class="col-md-4 col-xs-6">
                    <div class="featured-box">
                        <div class="featured-header">
                            <div class="featured-bg" style="background-color: #fee3a9"></div>
                            <div class="stuff">
                                <div class="featured-title">
                                    <i title="support me" class="fa fa-gift"></i>
                                    <a href="/support-me" class="supporter">
                                        <strong>Support AlwaysBeRunning.net</strong>
                                    </a>
                                </div>
                                <div class="featured-title">
                                    <em>thank you for using this site</em>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>