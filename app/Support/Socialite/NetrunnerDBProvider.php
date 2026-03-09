<?php

namespace App\Support\Socialite;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User;

class NetrunnerDBProvider extends AbstractProvider
{
    /**
     * Get the authentication URL for the provider.
     *
     * @param string $state
     * @return string
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://netrunnerdb.com/oauth/v2/auth', $state);
    }

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return 'https://netrunnerdb.com/oauth/v2/token';
    }

    /**
     * Get the token fields for the token request.
     *
     * NetrunnerDB requires the OAuth2 grant_type for authorization code exchange.
     *
     * @param string $code
     * @return array
     */
    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
        ]);
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param string $token
     * @return array
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://netrunnerdb.com/api/2.0/private/account/info', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        $user = json_decode((string) $response->getBody(), true);

        if (isset($user['data'][0]) && is_array($user['data'][0])) {
            return $user['data'][0];
        }

        return is_array($user) ? $user : [];
    }

    /**
     * Map the raw user array to a Socialite User object.
     *
     * @param array $user
     * @return \Laravel\Socialite\Two\User
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id' => array_key_exists('id', $user) ? $user['id'] : null,
            'nickname' => array_key_exists('username', $user) ? $user['username'] : null,
            'name' => array_key_exists('username', $user) ? $user['username'] : null,
            'email' => array_key_exists('email', $user) ? $user['email'] : null,
        ]);
    }
}
