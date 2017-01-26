<?php

namespace ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class UserController extends BaseController
{
    public function authAction(Request $request)
    {
        if (is_null($code = $request->query->get('code'))) {
            throw $this->createBadRequestException(
                '"code" parameter is required to go through the authentication process'
            );
        }

        try {
            $github = $this->get('api_bundle.github_client');

            return $this->getUser(
                $github->authUser($code)['access_token'],
                $github->getCurrentUser()['login']
            );
        } catch (\ApiBundle\Service\Github\Exception\Exception $e) {
            throw $this->createBadRequestException($e->getMessage(), $e);
        }
    }

    public function checkProfileAction()
    {
        return [
            'complete' => count(
                $this
                    ->get('validator')
                    ->validate($this->getUser(), null, ['complete_profile'])
                ) == 0
        ];
    }

    public function updateAction(Request $request)
    {
        return $this->handleForm(
            $request,
            \ApiBundle\Form\UserType::class,
            $this->getUser()
        );
    }
}
