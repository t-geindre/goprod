<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BaseController extends Controller
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

        throw $this->createBadRequestException('Bad credentials');
    }

    protected function createBadRequestException($message = 'Bad request', \Exception $previous = null)
    {
        return new BadRequestHttpException($message, $previous);
    }
}
