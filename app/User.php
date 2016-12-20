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
        'about', 'autofilter_upcoming', 'autofilter_results'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token',
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
}
