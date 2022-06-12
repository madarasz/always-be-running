<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // validators for tournament standing entry, top cut rank
        Validator::extend('tournament_top', function($attribute, $value, $parameters, $validator) {
            return ($parameters[0] <= $parameters[1] || $value == 0); // swiss rank should be in cut or top rank not set
        });
        Validator::extend('tournament_not_top', function($attribute, $value, $parameters, $validator) {
            return ($parameters[0] > $parameters[1] || $value != 0); // swiss rank should be out of cut or top rank set
        });
        // validator for tournament form conclusion / conclusion form
        Validator::extend('players_top', function($attribute, $value, $parameters, $validator) {
            return ($parameters[0] > $parameters[1]); // players number should be greater than the top cut player number
        });
        // validator for NRTM conclusion code
        Validator::extend('conc_code', function($attribute, $value, $parameters, $validator) {
            return (file_exists('tjsons/nrtm/import_'.$parameters[0].'.json')); // NRTM temp upload exists
        });
        // validator for tournament end dates
        Validator::extend('date_later', function($attribute, $value, $parameters, $validator) {
            $start = strtotime(substr(str_replace(".", "/", $parameters[0]),0,10));
            $end = strtotime(substr(str_replace(".", "/", $parameters[1]),0,10));
            return ($end > $start); // end is later
        });
        // validator for tournament end dates, max week for normal tournaments, max 8 weeks for async
        Validator::extend('date_later_max_week', function($attribute, $value, $parameters, $validator) {
            $start = strtotime(substr(str_replace(".", "/", $parameters[0]),0,10));
            $end = strtotime(substr(str_replace(".", "/", $parameters[1]),0,10));
            return ($end - $start <= 60 * 60 * 24 * 7 * ($parameters[2] == 12 ? 12 : 1) || $end <= $start); // end is max a week later
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
