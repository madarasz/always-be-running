<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TournamentGroup extends Model
{
    public $timestamps = true;
    protected $fillable = ['title', 'date', 'location', 'creator', 'description'];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    protected $hidden = ['created_at', 'updated_at'];
    protected $appends = ['tournamentCount'];

    public function creator() {
        return $this->hasOne(User::class, 'id', 'creator');
    }

    public function tournaments() {
        return $this->belongsToMany(Tournament::class, 'tournament_tournament_groups',
            'tournament_group_id', 'tournament_id');
    }

    public function getTournamentCountAttribute() {
        return $this->tournaments->count();
    }
}
