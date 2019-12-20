<div class="bracket" v-if="editMode">
    <h5>
        <i class="fa fa-paint-brush" aria-hidden="true"></i>
        Artist
    </h5>
    {{-- Register as artist--}}
    <div class="text-xs-center" v-if="!user.artist">
        <button type="button" class="btn btn-success" href="#" id="button-register-artist" v-cloak
                @click="registerArtist()">
            <i class="fa fa-paint-brush" aria-hidden="true"></i> Register as an Artist
        </button><br />
        <div class="legal-bullshit">
            You will be able to add a gallery of art items to your ABR profile.
        </div>
    </div>
    {{-- Registered artist --}}
    <div class="text-xs-center" v-if="user.artist">
        <div class="m-b-2">
            Congratulations! You are a registered artist.
        </div>
        <div>
            You can manage your artist gallery on the <a href="#tab-my-art">My art</a> tab of your profile.
        </div>
        <hr/>
        <button type="button" class="btn btn-danger" href="#" id="button-unregister-artist" v-cloak
                @click="unregisterArtist()">
            <i class="fa fa-times" aria-hidden="true"></i> Unregister as an Artist
        </button><br />
        <div class="legal-bullshit m-b-2">
            We will hide your gallery. Your items will be kept in the database and we can
            restore it if you re-register as an artist.
        </div>
    </div>
</div>