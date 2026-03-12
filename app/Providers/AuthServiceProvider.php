<?php

namespace App\Providers;

use App\Entry;
use App\Policies\EntryPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Tournament' => 'App\Policies\TournamentPolicy',
        Entry::class => EntryPolicy::class,
        'App\Model' => 'App\Policies\ModelPolicy',
        'App\Video' => 'App\Policies\VideoPolicy',
        'App\VideoTag' => 'App\Policies\VideoTagPolicy',
        'App\Photo' => 'App\Policies\PhotoPolicy',
        'App\TournamentGroup' => 'App\Policies\TournamentGroupPolicy'
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
