<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    protected $fillable = [
        'creator_id', 'name', 'url', 'description', 'user_id'
    ];
    protected $hidden = [
        'created_at', 'updated_at', 'creator_id', 'user_id', 'name'
    ];

    protected $appends = ['displayArtistName'];

    public $timestamps = true;
    protected $primaryKey = 'id';

    public function items() {
        return $this->hasMany(PrizeElement::class, 'artist_id', 'id');
    }

    public function user() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function displayArtistName() {
        $user = $this->user;
        if ($user === NULL) {
            return $this->name;
        }
        return $user->displayUsername();
    }

    public function getDisplayArtistNameAttribute() {
        return $this->displayArtistName();
    }
}
