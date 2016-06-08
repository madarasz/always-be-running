<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tournament extends Model
{
    use SoftDeletes;
    public $timestamps = true;
    protected $fillable = ['title', 'date', 'location_country', 'location_us_state', 'location_city', 'location_store',
        'location_address', 'players_number', 'description', 'concluded', 'decklist', 'top_number', 'creator',
        'tournament_type_id', 'start_time', 'cardpool_id', 'display_map', 'conflict'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function tournament_type() {
        return $this->hasOne(TournamentType::class, 'id', 'tournament_type_id');
    }

    public function user() {
        return $this->hasOne(User::class, 'id', 'creator');
    }

    public function entries() {
        return $this->hasMany(Entry::class, 'tournament_id', 'id');
    }

    public function country() {
        return $this->hasOne(Country::class, 'id', 'location_country');
    }

    public function state() {
        return $this->hasOne(UsState::class, 'id', 'location_us_state');
    }

    public function cardpool() {
        return $this->hasOne(CardPack::class, 'id', 'cardpool_id');
    }

    public function registration_number() {
        return $this->entries()->count();
    }

    public function claim_number() {
        return $this->entries()->whereNotNull('rank')->count();
    }
}
