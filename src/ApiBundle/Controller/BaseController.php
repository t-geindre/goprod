<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BaseController extends Controller
{
    protected function getUser($accessToken = null, $login = null)
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
                return $user;
            }
        }

        throw $this->createBadRequestException('Bad credentials');
    }

    protected function createBadRequestException($message = 'Bad request', \Exception $previous = null)
    {
        return new BadRequestHttpException($message, $previous);
    }

    protected function handleForm(Request $request, $type, $entity)
    {
        $form = $this->createForm($type, $entity);

        $form->submit(
            json_decode($request->getContent(), true)
        );

        $result = ['entity' => $entity];

        if ($valid = $form->isValid()) {
            $em = $this->get('doctrine')->getEntityManager();
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
