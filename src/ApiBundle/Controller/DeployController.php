<?php

namespace ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use ApiBundle\Criteria\Deploy\Active as ActiveDeploy;
use ApiBundle\Criteria\Deploy\ActiveByRepository as ActiveDeployByRepository;
use ApiBundle\Entity\Deploy;

class DeployController extends BaseController
{
    protected function getDeploy(int $id)
    {
        if (is_null($deploy = $this->get('api_bundle.repository.deploy')->find($id))) {
            throw $this->createNotFoundException('Deploy not found');
        }

        if ($deploy->getUser() != $this->getUser()) {
            throw $this->createBadRequestException('Bad credentials');
        }

        return $deploy;
    }

    public function createAction(Request $request)
    {
        return $this->handleForm(
            $request,
            \ApiBundle\Form\DeployType::class,
            (new \ApiBundle\Entity\Deploy)->setUser($this->getUser()),
            [$this->get('api_bundle.manager.deploy'), 'updateStatus']
        );
    }

    public function getAction(int $id)
    {
        $deploy = $this->getDeploy($id);
        $manager = $this->get('api_bundle.manager.deploy');

        $manager
            ->updateStatus($deploy)
            ->save($deploy);

        return $deploy;
    }

    public function cancelAction(int $id)
    {
        $this->get('api_bundle.manager.deploy')->save(
            $deploy = $this->getDeploy($id)
                ->setStatus(Deploy::STATUS_CANCELED)
        );

        return $deploy;
    }

    public function confirmAction(int $id)
    {
        $deploy = $this->getDeploy($id);

        if ($deploy->getStatus() != Deploy::STATUS_WAITING) {
            throw $this->createBadRequestException(
                'Cannot confirm deployment which is not in waiting status.'
            );
        }

        $this->get('api_bundle.manager.deploy')->save(
            $deploy = $this->getDeploy($id)
                ->setStatus(Deploy::STATUS_DONE)
        );

        return $deploy;
    }

    public function getByCurrentUserAction()
    {
        return $this->getUser()->getDeploys()
            ->matching((new ActiveDeploy)->build());
    }

    public function getByRepositoryAction(string $owner, string $repository)
    {
        return $this->get('api_bundle.repository.deploy')->matching(
            (new ActiveDeployByRepository($owner, $repository))
                ->build()
                ->orderBy(['createDate' => 'asc'])
        )->toArray();
    }
}
