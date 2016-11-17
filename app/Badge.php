<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = [
        'order', 'name', 'description', 'filename'
    ];

    public $timestamps = false;
    protected $primaryKey = 'id';

    public function users() {
        return $this->belongsToMany('App\User')->withPivot('seen');
    }
}
