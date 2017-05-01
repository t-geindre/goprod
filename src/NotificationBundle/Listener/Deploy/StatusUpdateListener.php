<?php

namespace NotificationBundle\Listener\Deploy;

use ApiBundle\Event\DeployStatusUpdateEvent;
use NotificationBundle\Notifier\AbstractNotifier;

/**
 * Listen deploy status updates
 */
class StatusUpdateListener
{
    /**
     * @var array
     */
    protected $notifiers = [];

    /**
     * @param DeployStatusUpdateEvent $event
     */
    public function onUpdate(DeployStatusUpdateEvent $event)
    {
        foreach ($this->notifiers as $notifier) {
            $notifier->notify($event->getDeploy(), $event->getOldStatus(), $event->getNewStatus());
        }
    }

    /**
     * @param AbstractNotifier $notifier
     */
    public function addNotifier(AbstractNotifier $notifier)
    {
        $this->notifiers[] = $notifier;
    }
}
