<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoTag extends Model
{
    public $timestamps = false;
    protected $fillable = ['video_id', 'user_id', 'tagged_by_user_id', 'is_runner', 'import_player_name'];
    protected $appends = ['entry'];
    protected $hidden = ['video', 'user_id'];

    public function video() {
        return $this->belongsTo(Video::class);
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getEntryAttribute() {
        return Entry::where('user', $this->user_id)->where('type', 3)
            ->where('tournament_id', $this->video->tournament->id)
            ->first();
    }
}
