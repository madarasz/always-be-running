<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CardIdentity extends Model
{

    protected $fillable = ['id', 'pack_code', 'faction_code', 'runner', 'title'];
    public $timestamps = false;

    // TODO: cardpack relation
}
