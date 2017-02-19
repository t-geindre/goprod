<?php
namespace ApiBundle\Listener\Doctrine;

use ApiBundle\Entity\Deploy as Entity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class Deploy
{
    protected $queuesToUpdate = [];
    protected $deployManager;
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

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

    public function updateQueues()
    {
        foreach ($this->queuesToUpdate as $queue) {
            $this->getManager()->updateQueue($queue['owner'], $queue['repository']);
        }
    }

    protected function isSupported($entity)
    {
        return $entity instanceof Entity;
    }

    protected function getManager()
    {
        if (is_null($this->deployManager)) {
            $this->deployManager = $this->container->get('api_bundle.manager.deploy');
        }

        return $this->deployManager;
    }
}
