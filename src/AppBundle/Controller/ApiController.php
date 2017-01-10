<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends Controller
{
    protected function jsonResponse($content, $code = 200, $headers = [])
    {
        return new \Symfony\Component\HttpFoundation\Response(
            $this->container->get('serializer')->serialize($content, 'json'),
            $code,
            array_merge(
                ['content-type' => 'application/json'],
                $headers
            )
        );
    }

    public function authAction(Request $request)
    {
        if (is_null($code = $request->query->get('code'))) {
            return $this->jsonResponse(
                ['error' => '"code" parameter is required to go through the authentication process'],
                400
            );
        }

        try {
            return $this->jsonResponse($this->get('app_bundle.github_client')->authUser($code));
        } catch (\AppBundle\Service\Github\Exception\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}
