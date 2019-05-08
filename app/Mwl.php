<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mwl extends Model
{
    protected $fillable = ['id', 'date', 'name'];
    public $timestamps = false;
}
