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
            <div class="col-form-label" v-if="!editMode" v-cloak>@{{ user.username_preferred }}</div>
            <input class="form-control" type="text" id="username_preferred" name="username_preferred" v-cloak
                   v-if="editMode" placeholder="leave empty to use NetrunnerDB" v-model="user.username_preferred">
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
            <div class="col-form-label" v-if="!editMode" v-cloak>@{{ user.username_real }}</div>
            <input class="form-control" type="text" id="username_real" v-if="editMode" v-cloak
                   name="username_real" v-model="user.username_real">
        </div>
    </div>
    <div class="form-group row">
        <label for="username_jinteki" class="col-xs-3 col-form-label">Jinteki.net:</label>
        <div class="col-xs-9">
            <div class="col-form-label" v-if="!editMode" v-cloak>@{{ user.username_jinteki }}</div>
            <input class="form-control" type="text" id="username_jinteki" v-if="editMode" v-cloak
                   name="username_jinteki" v-model="user.username_jinteki">
        </div>
    </div>
    <div class="form-group row">
        <label for="username_slack" class="col-xs-3 col-form-label">Stimhack.Slack:</label>
        <div class="col-xs-9">
            <div class="col-form-label" v-if="!editMode" v-cloak>
                @{{ user.username_slack }}
            </div>
            <input class="form-control" type="text" id="username_slack" v-if="editMode" v-cloak
                   name="username_slack" v-model="user.username_slack">
        </div>
    </div>
    <div class="form-group row">
        <label for="username_stimhack" class="col-xs-3 col-form-label">Stimhack forum:</label>
        <div class="col-xs-9">
            <div class="col-form-label" v-if="!editMode">
                <a :href="'https://forum.stimhack.com/users/' + user.username_stimhack" v-cloak>
                    @{{ user.username_stimhack }}
                </a>
            </div>
            <input class="form-control" type="text" id="username_stimhack" v-if="editMode" v-cloak
                   name="username_stimhack" v-model="user.username_stimhack">
        </div>
    </div>
    <div class="form-group row">
        <label for="username_twitter" class="col-xs-3 col-form-label">Twitter:</label>
        <div class="col-xs-9">
            <div class="col-form-label" v-if="!editMode">
                <a :href="'https://twitter.com/' + user.username_twitter" v-cloak>
                    @{{ user.username_twitter ? '@'+user.username_twitter : '' }}
                </a>
            </div>
            <input class="form-control" type="text" id="username_twitter" v-if="editMode" v-cloak
                   name="username_twitter" placeholder="username without @" v-model="user.username_twitter">
        </div>
    </div>
</div>