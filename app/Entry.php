<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    public $timestamps = true;
    protected $fillable = ['rank', 'rank_top', 'runner_deck_title', 'runner_deck_id', 'runner_deck_identity',
        'corp_deck_title', 'corp_deck_id', 'corp_deck_identity', 'approved', 'user', 'tournament_id', 'import_username',
        'runner_deck_type', 'corp_deck_type'];
    protected $dates = ['created_at', 'updated_at'];

    public function tournament() {
        return $this->belongsTo(Tournament::class, 'tournament_id', 'id');
    }

    public function player() {
        return $this->hasOne(User::class, 'id', 'user');
    }
}
