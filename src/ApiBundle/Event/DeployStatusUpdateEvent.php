<?php

namespace ApiBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use ApiBundle\Entity\Deploy;

/**
 * Deploy status updated event
 */
class DeployStatusUpdateEvent extends Event
{
    /**
     * @var string
     */
    const NAME = 'api_bundle.events.deploy.status.update';

    /**
     * @var Deploy
     */
    protected $deploy;

    /**
     * @var string
     */
    protected $oldStatus;

    /**
     * @var string
     */
    protected $newStatus;

    /**
     * @param Deploy $deploy
     * @param string $oldStatus
     * @param string $newStatus
     */
    public function __construct(Deploy $deploy, string $oldStatus = null, string $newStatus)
    {
        $this->deploy = $deploy;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * @return Deploy
     */
    public function getDeploy() : Deploy
    {
        return $this->deploy;
    }

    /**
     * @return string|null
     */
    public function getOldStatus()
    {
        return $this->oldStatus;
    }

    /**
     * @return string
     */
    public function getNewStatus() : string
    {
        return $this->newStatus;
    }
}
