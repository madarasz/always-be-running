<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PrizeElement extends Model
{
    public $timestamps = true;
    protected $fillable = ['prize_id', 'quantity', 'title', 'type', 'creator'];
    protected $dates = ['created_at', 'updated_at'];

    public function prize() {
        return $this->belongsTo(Prize::class, 'prize_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'creator', 'id');
    }

    public function photos() {
        return $this->hasMany(Photo::class, 'prize_element_id', 'id');
    }

    public function quantityToString() {
        if (is_null($this->quantity)) {
            return 'participation';
        } else if (intval($this->quantity) == 1) {
            return 'champion';
        } else if (is_numeric($this->quantity)) {
            return 'top '.$this->quantity;
        }  else {
            return $this->quantity;
        }
    }
}
