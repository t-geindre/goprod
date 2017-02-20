<?php

namespace ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use ApiBundle\Criteria\Deploy\Active as ActiveDeploy;
use ApiBundle\Criteria\Deploy\ActiveByRepository as ActiveDeployByRepository;
use ApiBundle\Criteria\Deploy\SearchFilters as DeploySearchFilters;
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

    public function getAllAction(Request $request)
    {
        $user = null;
        if ($request->query->has('user')) {
            $user = $this->get('api_bundle.repository.user')->find(
                $request->query->getInt('user')
            );
        }

        $repository = $this->get('api_bundle.repository.deploy');
        $criteria = (new DeploySearchFilters(
                $request->get('status'),
                $request->get('owner'),
                $request->get('repository'),
                $user
            ))
            ->build()
            ->orderBy([
                ($request->get('sortBy') ?? 'id') => ($request->get('sortOrder') ?? 'desc')
            ])
            ->setFirstResult($request->query->getInt('offset'))
            ->setMaxResults(
                $request->query->has('offset') ? $request->query->getInt('limit') : 10
            );

        try {
            return [
                'total' => $repository->matching($criteria)->count(),
                'items' => $repository->matching($criteria)->toArray()
            ];
        } catch (\InvalidArgumentException $e) {
            throw $this->createBadRequestException($e->getMessage());
        }
    }
}
