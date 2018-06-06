<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PrizeCollection extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id', 'prize_element_id', 'owning', 'trading', 'wanting'];
    protected $hidden = ['id', 'user_id'];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function prizeElement() {
        return $this->belongsTo(PrizeElement::class, 'prize_element_id', 'id');
    }
}
