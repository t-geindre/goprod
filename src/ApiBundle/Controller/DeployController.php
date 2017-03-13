<?php

namespace ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use ApiBundle\Criteria\Deploy\Active as ActiveDeploy;
use ApiBundle\Criteria\Deploy\ActiveByRepository as ActiveDeployByRepository;
use ApiBundle\Criteria\Deploy\SearchFilters as DeploySearchFilters;
use ApiBundle\Entity\Deploy;

/**
 * Deploy controller
 */
class DeployController extends BaseController
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function createAction(Request $request) : array
    {
        return $this->handleForm(
            $request,
            \ApiBundle\Form\DeployType::class,
            (new \ApiBundle\Entity\Deploy())->setUser($this->getUser()),
            [$this->get('api_bundle.manager.deploy'), 'updateStatus']
        );
    }

    /**
     * @param int $id
     *
     * @return Deploy
     */
    public function getAction(int $id) : Deploy
    {
        $deploy = $this->getDeploy($id, false);
        $manager = $this->get('api_bundle.manager.deploy');

        if ($deploy->getUser()->getId() == $this->getUser()->getId()) {
            $manager
                ->updateStatus($deploy)
                ->save($deploy);
        }

        return $deploy;
    }

    /**
     * @param int $id
     *
     * @return Deploy
     */
    public function cancelAction(int $id) : Deploy
    {
        $this->get('api_bundle.manager.deploy')->save(
            $deploy = $this->getDeploy($id)
                ->setStatus(Deploy::STATUS_CANCELED)
        );

        return $deploy;
    }

    /**
     * @param int $id
     *
     * @return Deploy
     */
    public function confirmAction(int $id) : Deploy
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

    /**
     * @param int $id
     *
     * @return Deploy
     */
    public function deployAction(int $id) : Deploy
    {
        return $this
            ->get('api_bundle.manager.deploy')
            ->deploy($this->getDeploy($id));
    }

    /**
     * @return array
     */
    public function getByCurrentUserAction() : array
    {
        return $this->getUser()->getDeploys()
            ->matching((new ActiveDeploy())->build())
            ->toArray();
    }

    /**
     * @param string $owner
     * @param string $repository
     *
     * @return array
     */
    public function getByRepositoryAction(string $owner, string $repository) : array
    {
        return $this->get('api_bundle.repository.deploy')->matching(
            (new ActiveDeployByRepository($owner, $repository))
                ->build()
                ->orderBy(['createDate' => 'asc'])
        )->toArray();
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function getAllAction(Request $request) : array
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
                ($request->get('sortBy') ?? 'id') => ($request->get('sortOrder') ?? 'desc'),
            ])
            ->setFirstResult($request->query->getInt('offset'))
            ->setMaxResults(
                $request->query->has('offset') ? $request->query->getInt('limit') : 10
            );

        try {
            return [
                'total' => $repository->matching($criteria)->count(),
                'items' => $repository->matching($criteria)->toArray(),
            ];
        } catch (\InvalidArgumentException $e) {
            throw $this->createBadRequestException($e->getMessage());
        }
    }

    /**
     * @param int  $id
     * @param bool $checkOwner
     *
     * @return Deploy
     */
    protected function getDeploy(int $id, bool $checkOwner = true) : Deploy
    {
        if (is_null($deploy = $this->get('api_bundle.repository.deploy')->find($id))) {
            throw $this->createNotFoundException('Deploy not found');
        }

        if ($checkOwner && $deploy->getUser() != $this->getUser()) {
            throw $this->createBadRequestException('Bad credentials');
        }

        return $deploy;
    }
}
