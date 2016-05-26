<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CardCycle extends Model
{
    protected $fillable = ['id', 'name', 'position'];
    public $timestamps = false;

    public function packs() {
        return $this->hasMany(CardPack::class, 'cycle_code', 'id');
    }
}
