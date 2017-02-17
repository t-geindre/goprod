<?php

namespace ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use ApiBundle\Criteria\Deploy\Active as ActiveDeploy;

class DeployController extends BaseController
{
    public function createAction(Request $request)
    {
        return $this->handleForm(
            $request,
            \ApiBundle\Form\DeployType::class,
            (new \ApiBundle\Entity\Deploy)->setUser($this->getUser()),
            [$this->get('api_bundle.manager.deploy'), 'updateStatus']
        );
    }

    public function getAction($id)
    {
        if (is_null($deploy = $this->get('api_bundle.repository.deploy')->find($id))) {
            throw $this->createNotFoundException('Deploy not found');
        }

        if ($deploy->getUser() != $this->getUser()) {
            throw $this->createBadRequestException('Bad credentials');
        }

        $em = $this->get('doctrine')->getManager();
        $this->get('api_bundle.manager.deploy')->updateStatus($deploy);
        $em->persist($deploy);
        $em->flush();

        return $deploy;
    }

    public function getByCurrentUserAction()
    {
        return $this->getUser()->getDeploys()
            ->matching((new ActiveDeploy)->build());
    }
}
