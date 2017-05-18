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

    public function users() {
        return $this->belongsToMany(User::class, 'video_tags', 'video_id', 'user_id');
    }

    public function videoTags() {
        return $this->HasMany(VideoTag::class);
    }

    public static function youtubeLookup($input) {
        if(strlen($input) == 11) {
            // video ID
            $video_id = $input;
        } else {
            // video URL
            try {
                $video_id = Youtube::parseVidFromURL($input);
            } catch(\Exception $e) {
                return false;
            }
        }
        $data = Youtube::getVideoInfo($video_id);

        if($data) {
            return [
                'video_id' => $video_id,
                'video_title' => $data->snippet->title,
                'thumbnail_url' => $data->snippet->thumbnails->default->url,
                'channel_name' => $data->snippet->channelTitle
            ];
        } else {
            return false;
        }
    }
}
