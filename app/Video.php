<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Alaouy\Youtube\Facades\Youtube;

class Video extends Model
{
    public $timestamps = true;
    protected $fillable = ['video_title', 'video_id', 'thumbnail_url', 'channel_name', 'tournament_id', 'user_id'];
    protected $dates = ['created_at', 'updated_at'];

    public function tournament() {
        return $this->belongsTo(Tournament::class, 'tournament_id', 'id');
    }

    public function populateFromYoutube() {
        $data = Youtube::getVideoInfo($this->video_id);
        return $this->update([
            'video_title' => $data->snippet->title,
            'thumbnail_url' => $data->snippet->thumbnails->default->url,
            'channel_name' => $data->snippet->channelTitle
        ]);
    }
}
