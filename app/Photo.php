<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    public $timestamps = true;
    protected $fillable = ['title', 'tournament_id', 'user_id', 'filename', 'title', 'approved',
        'prize_id', 'prize_element_id'];
    protected $dates = ['created_at', 'updated_at'];
    protected $appends = ['url', 'urlThumb'];

    public function tournament() {
        return $this->belongsTo(Tournament::class, 'tournament_id', 'id');
    }

    public function prize() {
        return $this->belongsTo(Prize::class, 'prize_id', 'id');
    }

    public function prize_element() {
        return $this->belongsTo(PrizeElement::class, 'prize_element_id', 'id');
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

    public function getUrlAttribute() {
        return $this->url();
    }

    public function getUrlThumbAttribute() {
        return $this->urlThumb();
    }
}
