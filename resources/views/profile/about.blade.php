<div class="bracket">
    <h5>
        <i class="fa fa-book" aria-hidden="true"></i>
        About
    </h5>
    {{--Country--}}
    <div class="form-group row">
        <label for="username_real" class="col-xs-3 col-form-label">country:</label>
        <div class="col-xs-9">
            <div class="col-form-label" v-if="!editMode">
                <span v-if="user.country_id > 0" v-cloak>
                    <img :src="'/img/flags/'+user.country.flag"/>
                    @{{ user.country.name }}
                </span>
                <span v-if="user.country_id == 0">
                    --- not set ---
                </span>
            </div>
            @if (@$countries)
                <select class="form-control" id="country_id" v-if="editMode" name="country_id"
                        v-model="user.country_id" v-cloak>
                    <option value="0">--- not set ---</option>
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}">
                            {{ $country->name }}
                        </option>
                    @endforeach
                </select>
                <div v-if="editMode" v-cloak>
                    <input name="autofilter_upcoming" type="checkbox" v-model="user.autofilter_upcoming">
                    <label for="autofilter_upcoming" class="small-text">
                        <em>use as default filter for Upcoming tournaments</em>
                    </label>
                </div>
                <div v-if="editMode" v-cloak>
                    <input name="autofilter_results" type="checkbox" v-model="user.autofilter_results">
                    <label for="autofilter_results" class="small-text">
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
            <div class="col-form-label" v-if="!editMode">
                <span id="faction_logo" class="icon" :class="'icon-'+user.favorite_faction"></span>
                <span id="faction_text" v-cloak>@{{ factionCodeToFactionTitle(user.favorite_faction) }}</span>
            </div>
            @if (@$factions)
                <select class="form-control" id="favorite_faction" :class="editMode ? '' : 'hidden-xs-up'"
                        name="favorite_faction" v-model="user.favorite_faction" v-cloak>
                    <option value="">--- not set ---</option>
                    @foreach($factions as $faction)
                        <option value="{{ $faction->faction_code }}"></option>
                    @endforeach
                </select>
            @endif
        </div>
    </div>
    {{--Website--}}
    <div class="form-group row">
        <label for="website" class="col-xs-3 col-form-label">website:</label>
        <div class="col-xs-9">
            <div class="col-form-label" v-if="!editMode">
                <a :href="user.website" v-cloak>@{{ user.website }}</a>
            </div>
            <input class="form-control" type="text" id="website" v-if="editMode" v-cloak
                   name="website" v-model="user.website" placeholder="http://...">
        </div>
    </div>
    {{--About--}}
    <div class="form-group row">
        <label for="about" class="col-xs-3 col-form-label">about me:</label>
        <div class="col-xs-9">
            <div class="markdown-content" v-html="markdownAbout" v-if="!editMode"></div>
            <div v-if="editMode">
                <textarea rows="6" cols="" name="about" class="form-control" v-model="user.about" v-cloak></textarea>
                <div class="pull-right">
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
    {{--Show tournament claims--}}
    <div class="form-group row" v-if="editMode" v-cloak>
        <label for="about" class="col-xs-3 col-form-label">claims chart:</label>
        <div class="col-xs-9">
            <input name="show_chart" type="checkbox" v-model="user.show_chart">
            <label for="show_chart" style="margin-top: 0.5rem;">
                <em>show Claims chart</em>
            </label>
        </div>
    </div>
</div>