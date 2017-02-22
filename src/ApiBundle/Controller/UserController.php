<?php

namespace ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\Criteria;

/**
 * User controller
 */
class UserController extends BaseController
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function authAction(Request $request) : array
    {
        if (is_null($code = $request->query->get('code'))) {
            throw $this->createBadRequestException(
                '"code" parameter is required to go through the authentication process'
            );
        }

        try {
            $github = $this->get('api_bundle.github_client');

            return [
                'access_token' => $github->authUser($code)['access_token'],
                'login' => $github->getCurrentUser()['login'],
            ];
        } catch (\ApiBundle\Service\Github\Exception\Exception $e) {
            throw $this->createBadRequestException($e->getMessage(), $e);
        }
    }

    /**
     * @return array
     */
    public function profileAction() : array
    {
        return [
            'user' => $this->getUser(),
            'complete' => count(
                $this
                    ->get('validator')
                    ->validate($this->getUser(), null, ['complete_profile'])
            ) == 0,
        ];
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function updateAction(Request $request) : array
    {
        return $this->handleForm(
            $request,
            \ApiBundle\Form\UserType::class,
            $this->getUser()
        );
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function searchAction(Request $request)
    {
        return $this->get('api_bundle.repository.user')->matching(
            Criteria::create()
                ->where(Criteria::expr()->contains('login', $request->get('q')))
                ->orWhere(Criteria::expr()->contains('name', $request->get('q')))
        )->toArray();
    }
}
