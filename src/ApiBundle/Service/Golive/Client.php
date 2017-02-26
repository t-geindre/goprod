<?php

namespace ApiBundle\Service\Golive;

use Buzz\Message\RequestInterface;

/**
 * Golive client
 */
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
    protected $accessToken;

    /**
     * @param \Buzz\Browser $client
     * @param string        $baseUrl
     * @param string|null   $accessToken
     */
    public function __construct(
        \Buzz\Browser $client,
        string $baseUrl,
        string $accessToken = null
    ) {
        $this->client = $client;
        $this->baseUrl = $baseUrl;
        $this->setAccessToken($accessToken);
    }

    /**
     * @param string|null $token
     *
     * @return Client
     */
    public function setAccessToken(string $token = null) : Client
    {
        $this->accessToken = $token;

        return $this;
    }

    /**
     * @return array
     */
    public function whoAmI() : array
    {
        return $this->request('auth/whoami');
    }

    /**
     * @param string $url
     * @param string $method
     * @param string $content
     * @param array  $headers
     *
     * @return array
     */
    public function request(
        string $url,
        string $method = RequestInterface::METHOD_GET,
        $content = '',
        array $headers = []
    ) : array {
        if (!is_null($this->accessToken)) {
            $headers = array_merge($headers, ['Authorization' => sprintf('token %s', $this->accessToken)]);
        }

        try {
            $response = $this->client->call($this->baseUrl.$url, $method, $headers, $content);
        } catch (\Buzz\Exception\RequestException $e) {
            throw new Exception\NetworkException(sprintf(
                'An error occurred while trying to join Golive, %s',
                lcfirst($e->getMessage())
            ));
        }

        $json = json_decode($response->getContent(), true);

        if ($response->getStatusCode() == 401) {
            throw new Exception\AuthFailException($json['message']);
        }

        return $json;
    }
}
