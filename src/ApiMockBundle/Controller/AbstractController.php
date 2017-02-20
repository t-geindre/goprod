<?php

namespace ApiMockBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ApiMockBundle\Entity\Mock;
use Symfony\Component\HttpFoundation\Response;

class AbstractController extends Controller
{
    protected function response(
        string $api,
        string $type,
        callable $filter = null,
        int $status = 200
    ) {
        $data = $this->getData($api, $type);

        if (!is_null($filter)) {
            $data = call_user_func($filter, $data);
        }

        $response = new Response(json_encode($data), $status);
        $response->headers->add(['Content-type' => 'application/json']);

        return $response;
    }

    protected function getData(string $api, string $type)
    {
        return array_map(
            function($mock) {
                return json_decode($mock->getPayload(), true);
            },
            $this
                ->get('api_mock.repository.mock')
                ->findBy(['api' => $api, 'type' => $type])
        );
    }
}
