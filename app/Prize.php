<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Prize extends Model
{
    public $timestamps = true;
    protected $fillable = ['year', 'type', 'tournament_type_id', 'description', 'ffg_url', 'creator',
        'order'];
    protected $dates = ['created_at', 'updated_at'];
    protected $appends = ['pictureCount', 'tournamentCount'];

    public function tournament_type() {
        return $this->belongsTo(TournamentType::class, 'tournament_type_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'creator', 'id');
    }

    public function elements() {
        return $this->hasMany(PrizeElement::class, 'prize_id', 'id')
            ->select(['*',
                \DB::raw('IF(CEIL(quantity) > 0, `quantity`, 50000) `sort_order`'),
                \DB::raw('IF(`quantity` IS NOT NULL, `quantity`, 1000000) `sort_order`'),
            ])
            ->orderByRaw('CAST(sort_order AS DECIMAL(10,2)) DESC'); // null first, string later, int last
    }

    public function tournaments()
    {
        return $this->hasMany(Tournament::class, 'prize_id', 'id');
    }

    public function photos() {
        return $this->hasMany(Photo::class, 'prize_id', 'id');
    }

    public function countPhotos() {
        $elementIds = $this->elements()->pluck('id');
        return $this->photos->count() + Photo::whereIn('prize_element_id', $elementIds)->count();
    }

    public function getPictureCountAttribute() {
        return $this->countPhotos();
    }

    public function getTournamentCountAttribute() {
        return $this->tournaments()->count();
    }
}
