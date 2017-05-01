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
        <label for="username_slack" class="col-xs-3 col-form-label">Stimhack.Slack:</label>
        <div class="col-xs-9">
            <div class="col-form-label profile-text">
                {{ $user->username_slack }}
            </div>
            <input class="form-control profile-field hidden-xs-up" type="text" id="username_slack"
                   name="username_slack" value="{{ $user->username_slack }}">
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
                <a href="https://twitter.com/{{ $user->username_twitter }}">
                    {{ $user->username_twitter ? '@'.$user->username_twitter : '' }}
                </a>
            </div>
            <input class="form-control profile-field hidden-xs-up" type="text" id="username_twitter"
                   name="username_twitter" placeholder="username without @" value="{{ $user->username_twitter }}">
        </div>
    </div>
</div>