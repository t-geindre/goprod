<?php

namespace ApiBundle\Listener\Doctrine;

use ApiBundle\Entity\Deploy as Entity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use ApiBundle\Manager\DeployManager;
use ApiBundle\Event\DeployStatusUpdateEvent;

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
     * @var Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Note: Container injection to avoid cyclic dependency
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->eventDispatcher = $container->get('event_dispatcher');
        $this->container = $container;
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        if ($this->isSupported($deploy = $event->getEntity())) {
            $this->eventDispatcher->dispatch(
                DeployStatusUpdateEvent::NAME,
                new DeployStatusUpdateEvent(
                    $deploy,
                    null,
                    $deploy->getStatus()
                )
            );
        }
    }

    /**
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(PreUpdateEventArgs $event)
    {
        $deploy = $event->getEntity();

        if ($this->isSupported($deploy) && $event->hasChangedField('status')) {
            $this->eventDispatcher->dispatch(
                DeployStatusUpdateEvent::NAME,
                new DeployStatusUpdateEvent(
                    $deploy,
                    $event->getOldValue('status'),
                    $deploy->getStatus()
                )
            );

            if (in_array($deploy->getStatus(), [Entity::STATUS_CANCELED, Entity::STATUS_DONE])) {
                // avoid duplicate entries
                $this->queuesToUpdate[$deploy->getOwner().$deploy->getRepository()] = [
                    'owner' => $deploy->getOwner(),
                    'repository' => $deploy->getRepository(),
                ];
            }
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
