<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use ApiBundle\Entity\User;

/**
 * Api base controller
 */
class BaseController extends Controller
{
    /**
     * @param string|null $accessToken
     * @param string|null $login
     *
     * @return User
     */
    protected function getUser(string $accessToken = null, string $login = null) : User
    {
        $request = $this->get('request_stack')->getMasterRequest();
        $accessToken = $accessToken ?? $request->get('access_token');
        $login = $login ?? $request->get('login');

        if (!is_null($login) && !is_null($accessToken)) {
            $user = $this
                ->get('doctrine')
                ->getRepository('ApiBundle\\Entity\\User')
                ->findOneBy(['login' => $login, 'accessToken' => $accessToken]);

            if (!is_null($user)) {
                $this->get('api_bundle.github_client')->setAccessToken($user->getAccessToken());

                return $user;
            }
        }

        throw $this->createBadRequestException('Bad credentials');
    }

    /**
     * @param string          $message
     * @param \Exception|null $previous
     */
    protected function createBadRequestException($message = 'Bad request', \Exception $previous = null)
    {
        return new BadRequestHttpException($message, $previous);
    }

    /**
     * @param Request       $request
     * @param object        $type
     * @param object        $entity
     * @param Callable|null $prePersist
     *
     * @return array
     */
    protected function handleForm(Request $request, $type, $entity, callable $prePersist = null) : array
    {
        $form = $this->createForm($type, $entity);

        $form->submit(
            json_decode($request->getContent(), true)
        );

        $result = ['entity' => $entity];

        if ($valid = $form->isValid()) {
            $em = $this->get('doctrine')->getManager();
            if (!is_null($prePersist)) {
                call_user_func($prePersist, $entity);
            }
            $em->persist($entity);
            $em->flush();
        } else {
            $result['errors'] = $this
                ->get('api_bundle.serializer.form.error')
                ->serializeErrors($form)
            ;
        }

        return $result;
    }
}
