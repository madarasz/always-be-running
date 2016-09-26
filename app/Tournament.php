<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tournament extends Model
{
    use SoftDeletes;
    public $timestamps = true;
    protected $fillable = ['title', 'date', 'location_country', 'location_state', 'location_city', 'location_store',
        'location_address', 'location_place_id', 'players_number', 'description', 'concluded', 'decklist', 'top_number', 'creator',
        'tournament_type_id', 'start_time', 'cardpool_id', 'conflict', 'contact'];
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

    public function cardpool() {
        return $this->hasOne(CardPack::class, 'id', 'cardpool_id');
    }

    public function registration_number() {
        return $this->entries()->where('user', '>', '0')->count();
    }

    public function claim_number() {
        return $this->entries()->whereNotNull('rank')->count();
    }

    /**
     * updates conflict flag according to conflicting claims
     */
    public function updateConflict() {
        $conflict_rank = Entry::where('tournament_id', $this->id)
            ->groupBy('rank')->havingRaw('count(rank) > 1')->first();
        $conflict_rank_top = Entry::where('tournament_id', $this->id)
            ->groupBy('rank_top')->havingRaw('count(rank) > 1')->first();
        $this->update(['conflict' => is_null($conflict_rank) && is_null($conflict_rank_top) ? 0 : 1]);
    }
}
