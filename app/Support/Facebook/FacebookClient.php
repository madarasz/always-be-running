<?php

namespace App\Support\Facebook;

use Facebook\Facebook;

class FacebookClient
{
    /**
     * @var \Facebook\Facebook
     */
    protected $facebook;

    public function __construct(Facebook $facebook = null)
    {
        if ($facebook) {
            $this->facebook = $facebook;
            return;
        }

        $this->facebook = new Facebook(config('laravel-facebook-sdk.facebook_config'));
        $this->facebook->setDefaultAccessToken($this->facebook->getApp()->getAccessToken());
    }

    /**
     * @param string $graphPath
     * @return array
     * @throws \Facebook\Exceptions\FacebookSDKException
     * @throws \Facebook\Exceptions\FacebookResponseException
     */
    public function getGraphNode($graphPath)
    {
        $response = $this->facebook->get($graphPath);

        return json_decode($response->getBody(), true);
    }
}
