<?php

namespace App;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tournament extends Model
{
    use SoftDeletes;
    public $timestamps = true;
    protected $fillable = ['title', 'date', 'location_country', 'location_state', 'location_city', 'location_store',
        'location_address', 'location_place_id', 'players_number', 'description', 'concluded', 'decklist', 'top_number', 'creator',
        'tournament_type_id', 'start_time', 'reg_time', 'cardpool_id', 'conflict', 'contact', 'import', 'location_lat', 'location_long',
        'recur_weekly', 'incomplete', 'link_facebook', 'tournament_format_id', 'end_date', 'concluded_by', 'concluded_at',
        'relax_conflicts', 'timezone', 'prize_id', 'prize_additional', 'mwl_id', 'online'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'concluded_at'];
    protected $hidden = ['tournament_type_id', 'tournament_format_id', 'cardpool_id', 'relax_conflicts', 'timezone', 'pivot'];
    protected $appends = ['seoUrl'];

    public function mwl() {
        return $this->hasOne(Mwl::class, 'id', 'mwl_id');
    }

    public function tournament_type() {
        return $this->hasOne(TournamentType::class, 'id', 'tournament_type_id');
    }

    public function tournament_format() {
        return $this->hasOne(TournamentFormat::class, 'id', 'tournament_format_id');
    }

    public function prize() {
        return $this->hasOne(Prize::class, 'id', 'prize_id');
    }

    public function user() {
        return $this->hasOne(User::class, 'id', 'creator');
    }

    public function concluder() {
        return $this->hasOne(User::class, 'id', 'concluded_by');
    }

    public function entries() {
        return $this->hasMany(Entry::class, 'tournament_id', 'id');
    }

    public function videos() {
        return $this->hasMany(Video::class, 'tournament_id', 'id')->where('flag_removed', false);
    }

    public function videos_all() {
        return $this->hasMany(Video::class, 'tournament_id', 'id');
    }

    public function unofficial_prizes() {
        return $this->hasMany(TournamentPrize::class, 'tournament_id', 'id');
    }

    public function groups() {
        return $this->belongsToMany(TournamentGroup::class, 'tournament_tournament_groups',
            'tournament_id', 'tournament_group_id');
    }

    // videos counting with optimized performance
    public function videosCount() {
        return $this->hasOne(Video::class)->selectRaw('tournament_id, count(*) as aggregate')->groupBy('tournament_id');
    }
    public function getVideosCountAttribute(){
        // if relation is not loaded already, let's do it first
        if ( !$this->relationLoaded('videosCount')) {
            $this->load('videosCount');
        }
        $related = $this->getRelation('videosCount');
        // then return the count directly
        return ($related) ? (int) $related->aggregate : 0;
    }

    public function photos() {
        return $this->hasMany(Photo::class, 'tournament_id', 'id')->where('approved', '!=', 0);
    }

    // photos counting with optimized performance
    public function photosCount() {
        return $this->hasOne(Photo::class)->selectRaw('tournament_id, count(*) as aggregate')->groupBy('tournament_id');
    }
    public function getPhotosCountAttribute(){
        // if relation is not loaded already, let's do it first
        if ( !$this->relationLoaded('photosCount')) {
            $this->load('photosCount');
        }
        $related = $this->getRelation('photosCount');
        // then return the count directly
        return ($related) ? (int) $related->aggregate : 0;
    }

    public function cardpool() {
        return $this->hasOne(CardPack::class, 'id', 'cardpool_id');
    }

    // registered users counting with optimized performance
    public function registrationCount() {
        return $this->hasOne(Entry::class)->selectRaw('tournament_id, count(*) as aggregate')
            ->where('user', '>', 0)->groupBy('tournament_id');
    }
    public function getRegistrationCountAttribute(){
        // if relation is not loaded already, let's do it first
        if ( !$this->relationLoaded('registrationCount')) {
            $this->load('registrationCount');
        }
        $related = $this->getRelation('registrationCount');
        // then return the count directly
        return ($related) ? (int) $related->aggregate : 0;
    }

    // registered users counting with optimized performance
    public function claimCount() {
        return $this->hasOne(Entry::class)->selectRaw('tournament_id, count(*) as aggregate')
            ->whereNotNull('runner_deck_id')->groupBy('tournament_id');
    }
    public function getClaimCountAttribute(){
        // if relation is not loaded already, let's do it first
        if ( !$this->relationLoaded('claimCount')) {
            $this->load('claimCount');
        }
        $related = $this->getRelation('claimCount');
        // then return the count directly
        return ($related) ? (int) $related->aggregate : 0;
    }

    /**
     * updates conflict flag according to conflicting claims
     */
    public function updateConflict() {
        if ($this->relax_conflicts) {
            $this->update(['conflict' => 0]);
        } else {
            $conflict_rank = Entry::where('tournament_id', $this->id)
                ->groupBy('rank')->havingRaw('count(rank) > 1')->first();
            $conflict_rank_top = Entry::where('tournament_id', $this->id)->where('rank_top', '>', 0)
                ->groupBy('rank_top')->havingRaw('count(rank_top) > 1')->first();
            $this->update(['conflict' => is_null($conflict_rank) && is_null($conflict_rank_top) ? 0 : 1]);
        }
    }

    public function recurDay() {
        if (is_null($this->recur_weekly)) {
            return null;
        } else {
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            return $days[$this->recur_weekly -1];
        }
    }

    public function seoTitle() {
        //Lower case everything
        $seoTitle = strtolower($this->title);
        //Make alphanumeric (removes all other characters)
        $seoTitle = preg_replace("/[^a-z0-9_\s-]/", "", $seoTitle);
        //Clean up multiple dashes or whitespaces
        $seoTitle = preg_replace("/[\s-]+/", " ", $seoTitle);
        //Convert whitespaces and underscore to dash
        $seoTitle = preg_replace("/[\s_]/", "-", $seoTitle);
        //escape special cases
        if (in_array($seoTitle, ['approve', 'register', 'reject', 'restore', 'unregister'])) {
            $seoTitle = $seoTitle."-";
        }
        return $seoTitle;
    }

    public function seoUrl() {
        return '/tournaments/'.$this->id.'/'.$this->seoTitle();
    }

    public function getSeoUrlAttribute() {
        return $this->seoUrl();
    }

    public function coverImage() {
        if (preg_match('/!\[([^\]]*)\]\(\K([^)]+)(?=\))/', $this->description, $match)) {
            return $match[0];
        }
        return null;
    }

    public function winner() {
        return $this->hasOne(Entry::class)
            ->select(['*', \DB::raw('IF(`rank_top` = 0, 1000000, `rank_top`) `rank_zero_top`')])
            ->where('rank_top', 1)->orWhere('rank', 1)->orderBy('rank_zero_top');
    }
    public function getWinnerAttribute(){
        // if relation is not loaded already, let's do it first
        if ( !$this->relationLoaded('winner')) {
            $this->load('winner');
        }
        return $this->getRelation('winner');
    }

    public function location() {
        if ($this->online) {
            return 'online';
        } else {
            if ($this->location_country === 'United States') {
                return $this->location_country.', '.$this->location_state.', '.$this->location_city;
            } else {
                return $this->location_country.', '.$this->location_city;
            }
        }
    }

    public function getTimezone() {
        if (is_null($this->timezone) && !$this->online) { // not online tournament
            // request timezone from google maps api
            $tzRequest = file_get_contents("https://maps.googleapis.com/maps/api/timezone/json?location=".
                $this->location_lat.','. $this->location_long.'&timestamp='.time($this->date).'&key='.ENV('GOOGLE_BACKEND_API'));
            try {
                $timezoneString = json_decode($tzRequest, true)['timeZoneId'];
                // save to DB
                $this->timezone = $timezoneString;
                $this->save();
            } catch (\Exception $ex) {
                Log::error("Could not get timezone for event: ".$this->title." (".$this->id.")");
            }
        }
        return $this->timezone;
    }

    public function calendarEntry() {
        $allday = $this->online || strlen($this->start_time) == 0;

        if (is_null($this->recur_weekly)) {
            // non recurring
            $start_time = $allday ? "12:00 AM" : $this->start_time;
            $start = str_replace(".", "-", substr($this->date, 0, 10)) . " " . $start_time;
            if (is_null($this->end_date)) {
                $end = str_replace(".", "-", substr($this->date, 0, 10)) . " 23:59";
            } else {
                $end = str_replace(".", "-", substr($this->end_date, 0, 10)) . " 23:59";
            }
        } else {
            // recurring
            $start = $this->recurDay() . " " . $this->start_time;
            $end = $this->recurDay() . " 23:59";
        }
        $days = ['','MO','TU','WE','TH','FR','SA', 'SU'];

        return [
            'start' => $start,
            'end' => $end,
            'timezone' => $this->getTimezone(),
            'title' => $this->title,
            'description' => strip_tags(\Markdown::convertToHtml($this->description)),
            'description_html' => \Markdown::convertToHtml($this->description),
            'location' => $this->location_address,
            'facebook_event' => strpos($this->link_facebook, '/events/') ? $this->link_facebook : '',
            'all_day_event' => $allday,
            'alarm_reminder' => $allday ? '-PT480M' : '-PT120M',
            'recurring' => is_null($this->recur_weekly) ? false : $days[$this->recur_weekly],
            'uid' => $this->id.'alwaysberunningnet',
            'url' => env('APP_URL').$this->seoUrl(),
            'filename' => $this->seoTitle().'.ics'
        ];
    }
}
