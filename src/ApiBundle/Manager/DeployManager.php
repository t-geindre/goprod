<?php

namespace ApiBundle\Manager;

use ApiBundle\Entity\Deploy;
use ApiBundle\Criteria\Deploy\ActiveByRepository;
use ApiBundle\Service\Github\Client as GithubClient;
use ApiBundle\Service\Golive\Client as GoliveClient;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Collection;

/**
 * Manage deploys
 */
class DeployManager
{
    /**
     * @var GithubClient
     */
    protected $github;

    /**
     * @var GoliveClient
     */
    protected $golive;

    /**
     * @var string
     */
    protected $goliveStage;

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityRepository $repository
     * @param GithubClient     $github
     * @param GoliveClient     $golive
     * @param string           $goliveStage
     * @param EntityManager    $entityManager
     */
    public function __construct(
        EntityRepository $repository,
        GithubClient $github,
        GoliveClient $golive,
        string $goliveStage,
        EntityManager $entityManager
    ) {
        $this->repository = $repository;
        $this->github = $github;
        $this->golive = $golive;
        $this->goliveStage = $goliveStage;
        $this->entityManager = $entityManager;
    }

    /**
     * Update deploy status to the next available status if possible
     *
     * @param Deploy $deploy
     *
     * @return DeployManager
     */
    public function updateStatus(Deploy $deploy) : DeployManager
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
        }

        if ($newStatus != $deploy->getStatus()) {
            $deploy->setStatus($newStatus);
            $this->updateStatus($deploy);
        }

        return $this;
    }

    /**
     * @param Deploy $deploy
     *
     * @return bool
     */
    public function canStart(Deploy $deploy) : bool
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

    /**
     * @param Deploy $deploy
     *
     * @return bool
     */
    public function isMerged(Deploy $deploy) : bool
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

    /**
     * @param Deploy $deploy
     *
     * @return bool
     */
    public function isDeployed(Deploy $deploy) : bool
    {
        if ($goliveId = $deploy->getGoliveId()) {
            if ($goliveDeploy = $this->golive->getDeployment($goliveId)) {
                if ($goliveDeploy['status'] == 'success') {
                    return true;
                }
            }
        }

        $project = $this->golive->getProject($deploy->getRepository());
        if (!is_null($project)) {
            return false;
        }

        return true;
    }

    /**
     * @param Deploy $deploy
     *
     * @return Deploy
     */
    public function deploy(Deploy $deploy) : Deploy
    {
        if ($deploy->getStatus() == Deploy::STATUS_DEPLOY
            && !is_null($this->golive->getProject($deploy->getRepository()))
        ) {
            if (!is_null($deployId = $deploy->getGoliveId())
                && ($this->golive->getDeployment($deployId)['status'] ?? '') != 'failure'
            ) {
                return $deploy;
            }

            $goliveDeploy = $this->golive->createDeployment(
                $deploy->getRepository(),
                $this->goliveStage
            );

            $deploy->setGoliveId($goliveDeploy['id']);
            $this->save($deploy);
        }

        return $deploy;
    }

    /**
     * @param Deploy $deploy
     *
     * @return DeployManager
     */
    public function save(Deploy $deploy) : DeployManager
    {
        $this->entityManager->persist($deploy);
        $this->entityManager->flush();

        return $this;
    }

    /**
     * @param string $owner
     * @param string $repository
     *
     * @return DeployManager
     */
    public function updateQueue(string $owner, string $repository) : DeployManager
    {
        $deploys = $this->getActiveDeploys($owner, $repository, 1, 0);
        if (!$deploys->isEmpty()
            && ($deploy = $deploys->first())->getStatus() == Deploy::STATUS_QUEUED
        ) {
            $deploy->setStatus(Deploy::STATUS_MERGE);
            $this->updateStatus($deploy);
            $this->save($deploy);
        }

        return $this;
    }

    /**
     * @param string   $owner
     * @param string   $repository
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return Collection
     */
    protected function getActiveDeploys(
        string $owner,
        string $repository,
        int $limit = null,
        int $offset = null
    ) : Collection {
        return $this->repository->matching(
            (new ActiveByRepository($owner, $repository))
                ->build()
                ->orderBy(['createDate' => 'asc'])
                ->setFirstResult($offset)
                ->setMaxResults($limit)
        );
    }
}
