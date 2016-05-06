<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    public function tournament() {
        return $this->belongsTo(Tournament::class);
    }
}
