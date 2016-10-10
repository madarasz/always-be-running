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
