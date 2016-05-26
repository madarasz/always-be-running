<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CardIdentity extends Model
{

    protected $fillable = ['id', 'pack_code', 'faction_code', 'runner', 'title'];
    public $timestamps = false;
    public $incrementing = false;

    public function pack() {
        return $this->hasOne(CardPack::class, 'id', 'pack_code');
    }
}
