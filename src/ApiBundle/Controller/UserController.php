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
            return $this->get('api_bundle.github_client')->authUser($code);
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
}
