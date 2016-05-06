<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    protected $fillable = ['type'];

    public function type() {
        return $this->hasOne(TournamentType::class);
    }

    public function entry() {
        return $this->hasMany(Entry::class);
    }
}
