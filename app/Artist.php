<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    protected $fillable = [
        'creator_id', 'name', 'url', 'description', 'user_id'
    ];
    protected $hidden = [
        'created_at', 'updated_at', 'creator_id'
    ];

    public $timestamps = true;
    protected $primaryKey = 'id';
}
