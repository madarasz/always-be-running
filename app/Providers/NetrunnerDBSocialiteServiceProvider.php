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
                array_key_exists('client_id', $config) ? $config['client_id'] : null,
                array_key_exists('client_secret', $config) ? $config['client_secret'] : null,
                $this->resolveRedirectUri($config)
            );
        });
    }

    /**
     * Resolve redirect URI from new and legacy configuration keys.
     *
     * @param array $config
     * @return string
     */
    private function resolveRedirectUri(array $config)
    {
        if (array_key_exists('redirect', $config) && !empty($config['redirect'])) {
            return $config['redirect'];
        }

        if (!array_key_exists('redirect_url', $config) || empty($config['redirect_url'])) {
            return rtrim(config('app.url'), '/').'/oauth2/redirect';
        }

        $redirectHost = trim($config['redirect_url']);
        if (strpos($redirectHost, 'http://') === 0 || strpos($redirectHost, 'https://') === 0) {
            return rtrim($redirectHost, '/').'/oauth2/redirect';
        }

        $protocol = config('app.env') === 'local' ? 'http' : 'https';

        return $protocol.'://'.trim($redirectHost, '/').'/oauth2/redirect';
    }
}
