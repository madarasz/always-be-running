<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    public $timestamps = true;
    protected $fillable = ['rank', 'rank_top', 'deck_title', 'deck_id', 'deck_version', 'approved', 'user',
        'tournament_id'];
    protected $dates = ['created_at', 'updated_at'];

    public function tournament() {
        return $this->belongsTo(Tournament::class, 'id', 'tournament_id');
    }

    public function player() {
        return $this->hasOne(User::class, 'id', 'user');
    }
}
