<?php

/**
 * The MIT License
 * Copyright (c) 2007 Andy Smith
 */
declare (strict_types=1);
namespace Share_On_Twitter\Abraham\TwitterOAuth;

class Token
{
    /** @var string */
    public $key;
    /** @var string */
    public $secret;
    /**
     * @param string $key    The OAuth Token
     * @param string $secret The OAuth Token Secret
     */
    public function __construct(?string $key, ?string $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }
    /**
     * Generates the basic string serialization of a token that a server
     * would respond to request_token and access_token calls with
     *
     * @return string
     */
    public function __toString() : string
    {
        return \sprintf('oauth_token=%s&oauth_token_secret=%s', \Share_On_Twitter\Abraham\TwitterOAuth\Util::urlencodeRfc3986($this->key), \Share_On_Twitter\Abraham\TwitterOAuth\Util::urlencodeRfc3986($this->secret));
    }
}
