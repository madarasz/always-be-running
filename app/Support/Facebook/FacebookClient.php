<?php

namespace App\Support\Facebook;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class FacebookClient
{
    protected $httpClient;
    protected $appId;
    protected $appSecret;

    public function __construct(?Client $httpClient = null)
    {
        $this->httpClient = $httpClient ?: new Client([
            'base_uri' => 'https://graph.facebook.com',
            'timeout' => 15,
            'http_errors' => false,
        ]);

        $config = (array) config('laravel-facebook-sdk.facebook_config', []);
        $this->appId = (string) ($config['app_id'] ?? '');
        $this->appSecret = (string) ($config['app_secret'] ?? '');
    }

    /**
     * @param string $graphPath
     * @return array
     * @throws \RuntimeException
     */
    public function getGraphNode($graphPath)
    {
        $path = ltrim((string) $graphPath, '/');
        $query = [];
        parse_str(parse_url($path, PHP_URL_QUERY) ?: '', $query);

        if ($this->appId !== '' && $this->appSecret !== '' && !isset($query['access_token'])) {
            $query['access_token'] = $this->appId . '|' . $this->appSecret;
        }

        $endpoint = parse_url($path, PHP_URL_PATH) ?: $path;

        try {
            $response = $this->httpClient->get($endpoint, ['query' => $query]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Facebook Graph API request failed: ' . $e->getMessage(), 0, $e);
        }

        $payload = json_decode((string) $response->getBody(), true);
        if (!is_array($payload)) {
            throw new \RuntimeException('Facebook Graph API returned invalid JSON');
        }

        if ($response->getStatusCode() >= 400) {
            $message = 'Facebook Graph API request failed';
            if (isset($payload['error']['message']) && is_string($payload['error']['message'])) {
                $message = $payload['error']['message'];
            }
            throw new \RuntimeException($message);
        }

        return $payload;
    }
}
