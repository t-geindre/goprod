<?php

namespace ApiMockBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ApiMockBundle\Entity\Mock;

class AbstractController extends Controller
{
    public function getMock()
    {
        return $this->get('api_mock.repository.mock');
    }

    public function decodePayloads(array $mocks)
    {
        return array_map([$this, 'decodePayload'], $mocks);
    }

    public function decodePayload(Mock $mock)
    {
        return json_decode($mock->getPayload(), true);
    }

    public function getData(string $api, string $type): array
    {
        return $this->decodePayloads(
            $this
                ->getMock()
                ->findBy(['api' => $api, 'type' => $type])
        );
    }
}
