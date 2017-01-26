<?php

namespace ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    protected function getUser(Request $request = null)
    {
        if (is_null($request)) {
            $request = $this->get('request_stack')->getMasterRequest();
        }

        if (
            !is_null($accessToken = $request->get('access_token'))
            && !is_null($login = $request->get('login'))
        ) {
            $user = $this
                ->get('doctrine')
                ->getRepository('ApiBundle\\Entity\\User')
                ->findOneBy(['login' => $login, 'accessToken' => $accessToken]);

            if (!is_null($user)) {
                return $user;
            }
        }

        throw $this->createAccessDeniedException('Bad credentials');
    }

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
            return $this->jsonResponse($this->get('api_bundle.github_client')->authUser($code));
        } catch (\ApiBundle\Service\Github\Exception\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function checkProfileAction()
    {
        return $this->jsonResponse([
            'complete' => count(
                $this
                    ->get('validator')
                    ->validate($this->getUser(), null, ['complete_profile'])
                ) == 0
        ]);
    }
}
