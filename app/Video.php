<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    public $timestamps = true;
    protected $fillable = ['video_title', 'video_id', 'thumbnail_url', 'channel_name', 'tournament_id', 'user_id',
        'type', 'length'];
    protected $dates = ['created_at', 'updated_at'];
    protected $hidden = ['tournament_id', 'user_id', 'created_at', 'updated_at'];

    public function tournament() {
        return $this->belongsTo(Tournament::class, 'tournament_id', 'id');
    }

    public function users() {
        return $this->belongsToMany(User::class, 'video_tags', 'video_id', 'user_id');
    }

    public function videoTags() {
        return $this->HasMany(VideoTag::class);
    }

}
