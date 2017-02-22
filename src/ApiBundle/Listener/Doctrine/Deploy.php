<?php
namespace ApiBundle\Listener\Doctrine;

use ApiBundle\Entity\Deploy as Entity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use ApiBundle\Manager\DeployManager;

/**
 * Deploy listener
 */
class Deploy
{
    /**
     * @var array
     */
    protected $queuesToUpdate = [];

    /**
     * @var DeployManager
     */
    protected $deployManager;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(PreUpdateEventArgs $event)
    {
        $deploy = $event->getEntity();

        if ($this->isSupported($deploy)
            && $event->hasChangedField('status')
            && in_array($deploy->getStatus(), [Entity::STATUS_CANCELED, Entity::STATUS_DONE])
        ) {
            // avoid duplicate entries
            $this->queuesToUpdate[$deploy->getOwner().$deploy->getRepository()] = [
                'owner' => $deploy->getOwner(),
                'repository' => $deploy->getRepository(),
            ];
        }
    }

    /**
     * Update deploys queues
     */
    public function updateQueues()
    {
        foreach ($this->queuesToUpdate as $queue) {
            $this->getManager()->updateQueue($queue['owner'], $queue['repository']);
        }
    }

    /**
     * @param object $entity
     *
     * @return boolean
     */
    protected function isSupported($entity) : bool
    {
        return $entity instanceof Entity;
    }

    /**
     * @return DeployManager
     */
    protected function getManager() : DeployManager
    {
        if (is_null($this->deployManager)) {
            $this->deployManager = $this->container->get('api_bundle.manager.deploy');
        }

        return $this->deployManager;
    }
}
