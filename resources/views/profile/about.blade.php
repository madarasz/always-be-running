<div class="bracket">
    <h5>
        <i class="fa fa-book" aria-hidden="true"></i>
        About
    </h5>
    {{--Country--}}
    <div class="form-group row">
        <label for="username_real" class="col-xs-3 col-form-label">country:</label>
        <div class="col-xs-9">
            <div class="col-form-label profile-text">
                @if ($user->country)
                    <img src="/img/flags/{{ $user->country->flag }}"/>
                    {{ $user->country->name }}
                @else
                    --- not set ---
                @endif
            </div>
            @if (@$countries)
                <select class="form-control profile-field hidden-xs-up" id="country_id"
                        name="country_id">
                    <option value="0">--- not set ---</option>
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}" {{ $user->country_id == $country->id ? 'selected' : '' }}>
                            {{ $country->name }}
                        </option>
                    @endforeach
                </select>
                <div>
                    {!! Form::checkbox('autofilter_upcoming', null,
                        in_array($user->autofilter_upcoming, [1, '1', 'on'], true),
                        ['id' => 'autofilter_upcoming', 'class' => 'profile-field hidden-xs-up']) !!}
                    <label for="autofilter_upcoming" class="small-text profile-field {{ $user->autofilter_upcoming ? '' :'hidden-xs-up' }}">
                        <em>use as default filter for Upcoming tournaments</em>
                    </label>
                </div>
                <div>
                    {!! Form::checkbox('autofilter_results', null,
                        in_array($user->autofilter_results, [1, '1', 'on'], true),
                        ['id' => 'autofilter_results', 'class' => 'profile-field hidden-xs-up']) !!}
                    <label for="autofilter_results" class="small-text profile-field {{ $user->autofilter_results ? '' :'hidden-xs-up' }}">
                        <em>use as default filter for tournament Results</em>
                    </label>
                </div>
            @endif
        </div>
    </div>
    {{--Favorite faction--}}
    <div class="form-group row">
        <label for="favorite_faction" class="col-xs-3 col-form-label">favorite faction:</label>
        <div class="col-xs-9">
            <div class="col-form-label profile-text">
                <span id="faction_logo" class="icon"></span>
                <span id="faction_text"></span>
            </div>
            @if (@$factions)
                <select class="form-control profile-field hidden-xs-up" id="favorite_faction"
                        name="favorite_faction">
                    <option value="">--- not set ---</option>
                    @foreach($factions as $faction)
                        <option value="{{ $faction->faction_code }}" {{ $user->favorite_faction === $faction->faction_code ? 'selected' : ''}}></option>
                    @endforeach
                </select>
            @endif
        </div>
    </div>
    {{--Website--}}
    <div class="form-group row">
        <label for="website" class="col-xs-3 col-form-label">website:</label>
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