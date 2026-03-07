<?php

namespace App\Providers;

use Artdarek\OAuth\OAuth;
use Artdarek\OAuth\TokenStorage;
use Illuminate\Support\ServiceProvider;
use OAuth\ServiceFactory;

class OAuthServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('oauth', function ($app) {
            $serviceFactory = new ServiceFactory();
            $tokenStorage = new TokenStorage($app->make('session'));

            return new OAuth($serviceFactory, $tokenStorage);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['oauth'];
    }
}
