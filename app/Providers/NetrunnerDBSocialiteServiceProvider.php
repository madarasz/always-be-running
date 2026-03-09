<?php

namespace App\Providers;

use App\Support\Socialite\NetrunnerDBProvider;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

class NetrunnerDBSocialiteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Socialite::extend('netrunnerdb', function ($app) {
            $config = $app['config']['services.netrunnerdb'];

            return new NetrunnerDBProvider(
                $app['request'],
                $config['client_id'] ?? null,
                $config['client_secret'] ?? null,
                $this->resolveRedirectUri($config)
            );
        });
    }

    /**
     * Resolve redirect URI from configuration.
     *
     * @param array $config
     * @return string
     * @throws \RuntimeException
     */
    private function resolveRedirectUri(array $config)
    {
        $redirect = array_key_exists('redirect', $config) ? trim((string) $config['redirect']) : '';
        if ($redirect === '') {
            throw new \RuntimeException('NETRUNNERDB_REDIRECT_URI is not configured.');
        }

        return $redirect;
    }
}
