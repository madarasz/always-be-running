<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Webpatser\Countries\Countries;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'id', 'sharing', 'email', 'published_decks', 'private_decks', 'reputation', 'country_id', 'favorite_faction',
        'username_real', 'username_preferred', 'username_jinteki', 'username_stimhack', 'username_twitter', 'website',
        'about', 'autofilter_upcoming', 'autofilter_results', 'username_slack', 'show_chart', 'secret_id',
        'prize_owning_public', 'prize_trading_public', 'prize_wanting_public',
        'prize_owning_text', 'prize_trading_text', 'prize_wanting_text', 'artist_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token', 'created_at', 'updated_at', 'admin', 'sharing', 'email', 'published_decks', 'private_decks',
        'username_real', 'username_jinteki', 'username_stimhack', 'username_twitter', 'website', 'about', 'reputation',
        'country_id', 'favorite_faction', 'autofilter_upcoming', 'autofilter_results', 'username_slack', 'show_chart',
        'secret_id'
    ];

    protected $appends = ['linkClass', 'displayUsername'];

    protected $primaryKey = 'id';

    public function displayUsername() {
        if ($this->username_preferred) {
            return $this->username_preferred;
        } else {
            return $this->name;
        }
    }

    public function getDisplayUsernameAttribute() {
        return $this->displayUsername();
    }

    public function getDisplayUsernameLowerAttribute() {
        return strtolower($this->displayUsername());
    }

    public function artist() {
        return $this->hasOne(Artist::class, 'id', 'artist_id');
    }

    public function badges() {
        return $this->belongsToMany('App\Badge', 'badge_user', 'user_id', 'badge_id')->withPivot('seen')->orderBy('order');
    }

    public function country() {
        return $this->hasOne(Countries::class, 'id', 'country_id');
    }

    public function videos() {
        return $this->belongsToMany(Video::class, 'video_tags', 'user_id', 'video_id')->where('flag_removed', false);
    }

    public function claims() {
        return $this->hasMany(Entry::class, 'user', 'id')->where('type', '>=', 3);
    }

    public function tournamentsCreated() {
        return $this->hasMany(Tournament::class, 'creator', 'id');
    }

    public function prizeElements() {
        return $this->hasMany(PrizeUser::class, 'user_id', 'id');
    }

    /**
     * Return CSS class for user's link
     */
    public function linkClass() {
        if ($this->admin) {
            return "admin";
        }
        if ($this->artist()) {
            return "artist";
        }
        if ($this->supporter) {
            return "supporter";
        }
        return "";
    }

    public function getLinkClassAttribute() {
        return $this->linkClass();
    }

    public function communityCount() {
        $tournament_ids = Tournament::where('creator', $this->id)->pluck('id');
        return Entry::whereIn('tournament_id', $tournament_ids)->whereIn('type', [3,4])->where('user', '!=', $this->id)->distinct('user')->count('user');
    }

    public function getSecretId() {
        if (is_null($this->secret_id)) {
            $this->secret_id = hash('md5', $this->id.env('APP_KEY'), false);
            $this->save();
        }
        return $this->secret_id;
    }
}
