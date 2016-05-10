<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    protected $fillable = ['title', 'date', 'location_country', 'location_us_state', 'location_city', 'location_store',
        'location_address', 'players_number', 'description', 'concluded', 'decklist', 'top_number', 'creator',
        'tournament_type_id'];

    public function tournament_type() {
        return $this->hasOne(TournamentType::class);
    }

    public function entry() {
        return $this->hasMany(Entry::class);
    }

    public function location_country() {
        return $this->hasOne(Country::class);
    }
}
