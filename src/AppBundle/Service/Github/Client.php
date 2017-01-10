<?php

namespace AppBundle\Service\Github;

use Buzz\Message\RequestInterface;

class Client
{
    /**
     * @var Buzz\Browser
     */
    protected $client;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @param Buzz\Browser $client
     */
    public function __construct(
        \Buzz\Browser $client,
        $baseUrl,
        $clientId,
        $clientSecret,
        $accessToken = null
    ) {
        $this->client = $client;
        $this->baseUrl = $baseUrl;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->accessToken = $accessToken;
    }

    public function authUser()
    {
        $authResponse = $this->request(
            'login/oauth/access_token',
            RequestInterface::METHOD_POST,
            [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code
            ]
        );

        if (is_null($authResponse) || empty($authResponse['access_token'])) {
            throw new Exception\AuthFailException(sprintf(
                'Github authentication failed, %s',
                lcfirst($authResponse['error_description'] ?? 'unknow reason')
            ));
        }

        $this->accessToken = $authResponse['access_token'];

        // @todo: throw event to register user in database
        return array_merge($authResponse, $this->getUser());
    }

    public function getUser($userName = null)
    {
        return $this->request('api/v3/user'.($userName ? '/'.$userName : ''));
    }

    public function request($url, $method = RequestInterface::METHOD_GET, $content = '', $headers = array())
    {
        $url = $this->baseUrl.$url;
        if (!is_null($this->accessToken)) {
            $url .= (parse_url($url, PHP_URL_QUERY) ? '&' : '?').'access_token='.$this->accessToken;
        }

        try {
            return json_decode(
                $this->client->call(
                    $url,
                    $method,
                    array_merge($headers, ['accept' => 'application/json']),
                    $content
                )->getContent(),
                true
            );
        } catch (\Buzz\Exception\RequestException $e) {
            throw new Exception\NetworkException(sprintf(
                'An error occurred while trying to join Github, %s',
                lcfirst($e->getMessage())
            ));
        }
    }
}
