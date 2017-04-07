<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    public $timestamps = true;
    protected $fillable = ['title', 'tournament_id', 'user_id', 'filename', 'title', 'approved'];
    protected $dates = ['created_at', 'updated_at'];

    public function tournament() {
        return $this->belongsTo(Tournament::class, 'tournament_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function url() {
        return '/photo/'.$this->filename;
    }

    public function urlThumb() {
        return '/photo/thumb_'.$this->filename;
    }
}
