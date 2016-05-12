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
        'tournament_type_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

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
