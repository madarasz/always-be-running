<?php

namespace OAuth\OAuth2\Service;

use Illuminate\Support\Facades\Auth;
use OAuth\Common\Token\TokenInterface;
use OAuth\OAuth2\Token\StdOAuth2Token;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Token\Exception\ExpiredTokenException;

class NetrunnerDB extends AbstractService
{

    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        $scopes = array(),
        UriInterface $baseApiUri = null
    ) {
        parent::__construct($credentials, $httpClient, $storage, $scopes, $baseApiUri);

        if (null === $baseApiUri) {
            $this->baseApiUri = new Uri('https://netrunnerdb.com/api/2.0/');
        }
    }

    /**
     * Parses the access token response and returns a TokenInterface.
     *
     *
     * @param string $responseBody
     *
     * @return TokenInterface
     *
     * @throws TokenResponseException
     */
    protected function parseAccessTokenResponse($responseBody)
    {
        $data = json_decode($responseBody, true);

        if (null === $data || !is_array($data)) {
            throw new TokenResponseException('Unable to parse response.');
        } elseif (isset($data['error'])) {
            throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
        }
        $token = new StdOAuth2Token();
        $token->setAccessToken($data['access_token']);
        if (isset($data['expires_in'])) {
            $token->setLifeTime($data['expires_in']);
            unset($data['expires_in']);
        }

        if (isset($data['refresh_token'])) {
            $token->setRefreshToken($data['refresh_token']);
            unset($data['refresh_token']);
        }
        unset($data['access_token']);

        $token->setExtraParams($data);
        return $token;
    }

    /**
     * Returns the authorization API endpoint.
     *
     * @return UriInterface
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://netrunnerdb.com/oauth/v2/auth');
    }

    /**
     * Returns the access token API endpoint.
     *
     * @return UriInterface
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://netrunnerdb.com/oauth/v2/token');
    }


    protected function getAuthorizationMethod()
    {
        return static::AUTHORIZATION_METHOD_QUERY_STRING;
    }

    /**
     * Adding a wrapper to the oauth request which tries the refresh token if the access token is expired.
     * If that fails also, it logs out the user.
     * @param $path
     * @param string $method
     * @param null $body
     * @param array $extraHeaders
     * @return null|string
     */
    public function requestWrapper($path, $method = 'GET', $body = null, array $extraHeaders = array())
    {
        try {
            return $this->request($path, $method, $body, $extraHeaders);
        } catch(\Exception $ex) {
            // refresh token
            try {
                $token = $this->storage->retrieveAccessToken($this->service());
                $this->refreshAccessToken($token);
                return $this->request($path, $method, $body, $extraHeaders);
            } catch(\Exception $ex) {
                // if all fails logout user
                \Log::alert("Coudn't refresh oauth token", $ex);
                Auth::logout();
                return '{ "data": [], "error": "Couldn\'t refresh oauth token"}';
            }
        }
    }
}