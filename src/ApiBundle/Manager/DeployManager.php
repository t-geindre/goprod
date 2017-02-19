<?php

namespace ApiBundle\Manager;

use ApiBundle\Entity\Deploy;
use ApiBundle\Criteria\Deploy\ActiveByRepository;
use ApiBundle\Service\Github\Client;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

class DeployManager
{
    protected $github;
    protected $repository;
    protected $entityManager;

    /**
     * @param EntityRepository $repository [description]
     */
    public function __construct(
        EntityRepository $repository,
        Client $github,
        EntityManager $entityManager
    ) {
        $this->repository = $repository;
        $this->github = $github;
        $this->entityManager = $entityManager;
    }

    /**
     * Update deploy status to the next available status if possible
     *
     * @param Deploy $deploy
     */
    public function updateStatus(Deploy $deploy)
    {
        $newStatus = $deploy->getStatus();

        switch ($deploy->getStatus()) {
            case Deploy::STATUS_NEW:
                $newStatus = Deploy::STATUS_QUEUED;
                break;

            case Deploy::STATUS_QUEUED:
                if ($this->canStart($deploy)) {
                    $newStatus = Deploy::STATUS_MERGE;
                }
                break;

            case Deploy::STATUS_MERGE:
                if ($this->isMerged($deploy)) {
                    $newStatus = Deploy::STATUS_DEPLOY;
                }
                break;

            case Deploy::STATUS_DEPLOY:
                if ($this->isDeployed($deploy)) {
                    $newStatus = Deploy::STATUS_WAITING;
                }
                break;

            case Deploy::STATUS_WAITING:

            default:
                return;
        }

        if ($newStatus != $deploy->getStatus()) {
            $deploy->setStatus($newStatus);
            $this->updateStatus($deploy);
        }

        return $this;
    }


    public function canStart(Deploy $deploy)
    {
        if ($deploy->getStatus() != Deploy::STATUS_QUEUED) {
            return false;
        }

        $deploys = $this->getActiveDeploys($deploy->getOwner(), $deploy->getRepository(), 1, 0);

        if ($deploys->isEmpty()) {
            return true;
        }

        if ($deploys->first()->getId() == $deploy->getId()) {
            return true;
        }

        return false;
    }

    public function isMerged(Deploy $deploy)
    {
        if (is_null($deploy->getPullRequestId())
            || $this->github->getPullRequest(
                $deploy->getOwner(),
                $deploy->getRepository(),
                $deploy->getPullRequestId()
            )['merged'] ?? false
        ) {
            return true;
        }

        return false;
    }

    public function isDeployed(Deploy $deploy)
    {
        return true;
        // has golive config
    }

    public function save(Deploy $deploy)
    {
        $this->entityManager->persist($deploy);
        $this->entityManager->flush();

        return $this;
    }

    public function updateQueue(string $owner, string $repository)
    {
        $deploys = $this->getActiveDeploys($owner, $repository, 1, 0);
        if (!$deploys->isEmpty()
            && ($deploy = $deploys->first())->getStatus() == Deploy::STATUS_QUEUED
        ) {
            $deploy->setStatus(Deploy::STATUS_MERGE);
            $this->updateStatus($deploy);
            $this->save($deploy);
        }
    }

    protected function getActiveDeploys(
        string $owner,
        string $repository,
        int $limit = null,
        int $offset = null
    ) {
        return $this->repository->matching(
            (new ActiveByRepository($owner, $repository))
                ->build()
                ->orderBy(['createDate' => 'asc'])
                ->setFirstResult($offset)
                ->setMaxResults($limit)
        );
    }
}
