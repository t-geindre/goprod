<?php

namespace AppBundle\Service\Github\Event;

use Symfony\Component\EventDispatcher\Event;
use AppBundle\Service\Github\Client;

class UserLogin extends Event
{
    const NAME = 'github.client.user.login';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $response;

    /**
     * @param Client $github
     */
    public function __construct(Client $client, array $response)
    {
        $this->client = $client;
        $this->response = $response;
    }

    /**
     * @return Client
     */
    public function getClient() : Client
    {
        return $this->client;
    }

    /**
     * @return array
     */
    public function getResponse() : array
    {
        return $this->response;
    }

    /**
     * @param array $response
     *
     * @return UserLogin
     */
    public function setResponse(array $response)
    {
        $this->response = $response;

        return $this;
    }
}
