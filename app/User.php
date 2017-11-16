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
        'about', 'autofilter_upcoming', 'autofilter_results', 'username_slack', 'show_chart'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token', 'created_at', 'updated_at', 'admin', 'sharing', 'email', 'published_decks', 'private_decks',
        'username_real', 'username_jinteki', 'username_stimhack', 'username_twitter', 'website', 'about', 'reputation',
        'country_id', 'favorite_faction', 'autofilter_upcoming', 'autofilter_results', 'username_slack', 'show_chart'
    ];

    protected $primaryKey = 'id';

    public function displayUsername() {
        if ($this->username_preferred) {
            return $this->username_preferred;
        } else {
            return $this->name;
        }
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

    /**
     * Return CSS class for user's link
     */
    public function linkClass() {
        if ($this->admin) {
            return "admin";
        }
        if ($this->supporter) {
            return "supporter";
        }
        return "";
    }

    public function communityCount() {
        $tournament_ids = Tournament::where('creator', $this->id)->pluck('id');
        return Entry::whereIn('tournament_id', $tournament_ids)->whereIn('type', [3,4])->where('user', '!=', $this->id)->distinct('user')->count('user');
    }
}
