<div class="tab-pane active" id="tab-info" role="tabpanel">
    <div class="row">
        <div class="col-md-4 col-xs-12">
            {{--User info--}}
            @include('profile.info')
            {{--Badges--}}
            @include('profile.badges')
            {{--Claims--}}
            @if ($claim_count)
                @include('profile.claims', ['maxrows' => 8])
            @endif
            {{--Created tournaments--}}
            @if ($created_count)
                @include('profile.created', ['maxrows' => 8])
            @endif
        </div>
        <div class="col-md-8 col-xs-12">
            {!! Form::open(['id' => 'profile-form']) !!}
            {{--Usernames--}}
            @include('profile.usernames')
            {{--About--}}
            @include('profile.about')
            {{-- Artist --}}
            @include('profile.artist')
            {{--second save button--}}
            <div class="text-xs-center">
                <button type="button" class="btn btn-info" href="#" id="button-save2" v-if="editMode" v-cloak
                        @click="saveProfile()">
                    <i class="fa fa-pencil" aria-hidden="true"></i> Save
                </button>
            </div>
            {!! Form::close() !!}
            {{--Tournament progress chart--}}
            @include('profile.tournament-chart')
            {{--Videos--}}
            @include('profile.videos')
        </div>
    </div>

    @include('tournaments.partials.icons', ['profile' => true])<br/>

    {{--Flaticon legal--}}
    @include('partials.legal-icons')
</div>