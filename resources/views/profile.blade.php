@extends('layout.general')

@section('content')
    {!! Form::open(['url' => '/profile/'.$user->id, 'id' => 'profile-form']) !!}
    <h4 class="page-header p-b-1">
        {{--Edit button--}}
        @if (Auth::check() && Auth::user()->id == $user->id)
            <div class="pull-right">
                <a class="btn btn-primary" href="#" onclick="profileSwitchEdit()" id="button-edit">
                    <i class="fa fa-pencil" aria-hidden="true"></i> Edit
                </a>
                <a class="btn btn-secondary hidden-xs-up" href="#" onclick="profileSwitchView()" id="button-cancel">
                    <i class="fa fa-times" aria-hidden="true"></i> Cancel
                </a>
                <a class="btn btn-info hidden-xs-up" href="#" id="button-save"
                   onclick="document.getElementById('profile-form').submit()">
                    <i class="fa fa-pencil" aria-hidden="true"></i> Save
                </a>
            </div>
        @endif
        Profile - {{ $user->displayUsername() }}
    </h4>
    @include('partials.message')
    @include('errors.list')
    <div class="row">
        <div class="col-md-4 col-xs-12">
            {{--User info--}}
            <div class="bracket">
                <h5 class="p-b-1">
                    <i class="fa fa-user" aria-hidden="true"></i>
                    User
                </h5>
                <div class="text-xs-center p-b-1">
                    <h6>{{ $user->displayUsername() }}</h6>
                    <div class="user-counts">
                        {{ $created_count }} tournament{{ $created_count > 1 ? 's' : '' }} organized<br/>
                        {{ $claim_count }} tournament claim{{ $claim_count > 1 ? 's' : '' }}<br/>
                        {{ $user->published_decks }} published deck{{ $user->published_decks > 1 ? 's' : '' }}
                        @if ($user->private_decks)
                            <br/>
                            {{ $user->private_decks }} private deck{{ $user->private_decks > 1 ? 's' : '' }}
                        @endif
                    </div>
                </div>
            </div>
            {{--Badges--}}
            <div class="bracket">
                <h5 class="p-b-1">
                    <i class="fa fa-trophy" aria-hidden="true"></i>
                    Badges
                    @include('partials.popover', ['direction' => 'top', 'content' =>
                            'You receive badges for claiming spots on tournaments and/or creating them.<br/>
                            <br/>
                            <a href="/badges/">full list of badges</a>'])
                </h5>
                <div class="text-xs-center">
                    @forelse($user->badges as $badge)
                        <img src="/img/badges/{{ $badge->filename }}" alt="{{ $badge->name }}"/>
                    @empty
                        <div class="m-b-2 font-italic text-xs-center">no badges yet</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-md-8 col-xs-12">
            {{--Usernames--}}
            <div class="bracket">
                <h5>
                    <i class="fa fa-user-circle-o" aria-hidden="true"></i>
                    Usernames
                </h5>
                {{--NetrunnerDB--}}
                <div class="form-group row">
                    <label class="col-xs-3 col-form-label">NetrunnerDB:</label>
                    <div class="col-xs-9">
                        <div class="col-form-label">
                            <a href="https://netrunnerdb.com/en/profile/{{ $user->id }}">{{ $user->name }}</a>
                        </div>
                    </div>
                </div>
                {{--Preferred--}}
                <div class="form-group row">
                    <label for="username_preferred" class="col-xs-3 col-form-label">displayed username:</label>
                    <div class="col-xs-9">
                        <div class="col-form-label profile-text">{{ $user->username_preferred }}</div>
                        <input class="form-control profile-field hidden-xs-up" type="text" id="username_preferred" name="username_preferred"
                               placeholder="leave empty to use NetrunnerDB" value="{{ $user->username_preferred }}">
                    </div>
                </div>
                <hr/>
                <div class="legal-bullshit text-xs-center">
                    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                    WARNING: we cannot ensure the authenticity of these usernames
                </div>
                <div class="form-group row">
                    <label for="username_real" class="col-xs-3 col-form-label">real name:</label>
                    <div class="col-xs-9">
                        <div class="col-form-label profile-text">{{ $user->username_real }}</div>
                        <input class="form-control profile-field hidden-xs-up" type="text" id="username_real"
                               name="username_real" value="{{ $user->username_real }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="username_jinteki" class="col-xs-3 col-form-label">Jinteki.net:</label>
                    <div class="col-xs-9">
                        <div class="col-form-label profile-text">{{ $user->username_jinteki }}</div>
                        <input class="form-control profile-field hidden-xs-up" type="text" id="username_jinteki"
                               name="username_jinteki" value="{{ $user->username_jinteki }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="username_stimhack" class="col-xs-3 col-form-label">Stimhack forum:</label>
                    <div class="col-xs-9">
                        <div class="col-form-label profile-text">
                            <a href="https://forum.stimhack.com/users/{{ $user->username_stimhack }}">{{ $user->username_stimhack }}</a>
                        </div>
                        <input class="form-control profile-field hidden-xs-up" type="text" id="username_stimhack"
                               name="username_stimhack" value="{{ $user->username_stimhack }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="username_twitter" class="col-xs-3 col-form-label">Twitter:</label>
                    <div class="col-xs-9">
                        <div class="col-form-label profile-text">
                            <a href="https://twitter.com/{{ $user->username_twitter }}">{{ $user->username_twitter }}</a></div>
                        <input class="form-control profile-field hidden-xs-up" type="text" id="username_twitter"
                               name="username_twitter" placeholder="username without @" value="{{ $user->username_twitter }}">
                    </div>
                </div>
            </div>
            {{--About--}}
            <div class="bracket">
                <h5>
                    <i class="fa fa-book" aria-hidden="true"></i>
                    About
                </h5>
                {{--Website--}}
                <div class="form-group row">
                    <label for="username_real" class="col-xs-3 col-form-label">website:</label>
                    <div class="col-xs-9">
                        <div class="col-form-label profile-text">
                            <a href="{{ $user->website }}">{{ $user->website }}</a>
                        </div>
                        <input class="form-control profile-field hidden-xs-up" type="text" id="website"
                               name="website" value="{{ $user->website }}" placeholder="http://...">
                    </div>
                </div>
                {{--About--}}
                <div class="form-group row">
                    <label for="about" class="col-xs-3 col-form-label">about me:</label>
                    <div class="col-xs-9">
                        <div class="col-form-label profile-text markdown-content">
                            {!! Markdown::convertToHtml(str_replace(["\r\n", "\r", "\n"], "  \r", $user->about)) !!}
                        </div>
                        {!! Form::textarea('about', $user->about, ['rows' => 6, 'cols' => '', 'class' => 'form-control profile-field hidden-xs-up']) !!}
                        <div class="pull-right profile-field hidden-xs-up">
                            <small><a href="http://commonmark.org/help/" target="_blank" rel="nofollow"><img src="/img/markdown_icon.png"/></a> formatting is supported</small>
                            @include('partials.popover', ['direction' => 'top', 'content' =>
                                    '<a href="http://commonmark.org/help/" target="_blank">Markdown cheat sheet</a><br/>
                                    <br/>
                                    How to make your tournament look cool?<br/>
                                    <a href="/markdown" target="_blank">example formatted description</a>'])
                        </div>
                    </div>
                </div>
            </div>
            {{--second save button--}}
            <div class="text-xs-center">
                <a class="btn btn-info hidden-xs-up" href="#" id="button-save2"
                   onclick="document.getElementById('profile-form').submit()">
                    <i class="fa fa-pencil" aria-hidden="true"></i> Save
                </a>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
    <script type="text/javascript">
        function profileSwitchEdit() {
            $('.profile-text').addClass('hidden-xs-up');
            $('.profile-field').removeClass('hidden-xs-up');
            $('#button-save').removeClass('hidden-xs-up');
            $('#button-save2').removeClass('hidden-xs-up');
            $('#button-cancel').removeClass('hidden-xs-up');
            $('#button-edit').addClass('hidden-xs-up');
        }
        function profileSwitchView() {
            $('.profile-text').removeClass('hidden-xs-up');
            $('.profile-field').addClass('hidden-xs-up');
            $('#button-save').addClass('hidden-xs-up');
            $('#button-save2').addClass('hidden-xs-up');
            $('#button-cancel').addClass('hidden-xs-up');
            $('#button-edit').removeClass('hidden-xs-up');
        }
    </script>
@stop

