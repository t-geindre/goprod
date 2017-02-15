<?php

namespace ApiBundle\Manager;

use ApiBundle\Entity\Deploy;

class DeployManager
{
    const STATUS_ORDER = [
        Deploy::STATUS_NEW,
        Deploy::STATUS_QUEUED,
        Deploy::STATUS_MERGE,
        Deploy::STATUS_DEPLOY,
        Deploy::STATUS_WAITING,
        Deploy::STATUS_DONE,
    ];

    protected $githubClient;
    protected $deployRepository;


    public function findRunningByUser()
    {

    }

    /**
     * Update deploy status to the next available status if possible
     *
     * @param Deploy $deploy
     */
    public function updateStatus(Deploy $deploy)
    {
        if (in_array($deploy->getStatus(), [Deploy::STATUS_NEW, Deploy::STATUS_QUEUED])) {

        }
    }

    protected function isDeployable(Deploy $deploy)
    {
        $this->deployRepository->findBy('');
    }
}
