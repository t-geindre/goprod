<?php

namespace ApiBundle\Manager;

use ApiBundle\Entity\Deploy;
use ApiBundle\Criteria\Deploy\ActiveByRepository;
use ApiBundle\Service\Github\Client;
use Doctrine\ORM\EntityRepository;

class DeployManager
{
    protected $github;
    protected $repository;

    /**
     * @param EntityRepository $repository [description]
     */
    public function __construct(EntityRepository $repository, Client $github)
    {
        $this->repository = $repository;
        $this->github = $github;
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
    }


    public function canStart(Deploy $deploy)
    {
        if ($deploy->getStatus() != Deploy::STATUS_QUEUED) {
            return false;
        }

        if ($this->repository
            ->matching(
                (new ActiveByRepository(
                    $deploy->getOwner(),
                    $deploy->getRepository()
                ))->build()
            )
            ->isEmpty()
        ) {
            $deploys = $this->repository->findBy(['status' => Deploy::STATUS_QUEUED], ['createDate' => 'ASC'], 1, 0);
            if (count($deploys) == 0
                || (
                    count($deploys) == 1
                    && array_pop($deploys)->getId() == $deploy->getId()
                )
            ) {
                return true;
            }
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
}
