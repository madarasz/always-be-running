<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TournamentPrize extends Model
{
    public $timestamps = false;
    protected $fillable = ['tournament_id', 'prize_element_id', 'quantity'];

    public function user() {
        return $this->belongsTo(Tournament::class, 'tournament_id', 'id');
    }

    public function prizeElement() {
        return $this->belongsTo(PrizeElement::class, 'prize_element_id', 'id');
    }
}
