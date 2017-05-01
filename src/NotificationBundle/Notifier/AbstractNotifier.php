<?php

namespace NotificationBundle\Notifier;

use ApiBundle\Entity\Deploy;
use ApiBundle\Service\Github\Client as GithubClient;

/**
 * Common notifiers functions
 */
abstract class AbstractNotifier
{
    /**
     * @var array
     */
    protected $notifications = [];

    /**
     * @var GithubClient
     */
    protected $githubClient;

    /**
     * @param array        $notifications
     * @param GithubClient $githubClient
     */
    public function __construct(array $notifications, GithubClient $githubClient)
    {
        $this->notifications = $notifications;
        $this->githubClient = $githubClient;
    }

    /**
     * @param Deploy      $deploy
     * @param string|null $oldStatus
     * @param string      $newStatus
     */
    abstract public function notify(Deploy $deploy, string $oldStatus = null, string $newStatus);

    /**
     * @param Deploy      $deploy
     * @param string|null $oldStatus
     * @param string      $newStatus
     */
    protected function getNotification(Deploy $deploy, string $oldStatus = null, string $newStatus)
    {
        if (is_null($status = $this->getStatus($oldStatus, $newStatus))) {
            return null;
        }

        foreach ($this->notifications as $notification) {
            if ((empty($notification['owner']) || $deploy->getOwner() == $notification['owner'])
                && (empty($notification['repository']) || $deploy->getRepository() == $notification['repository'])
                && $notification['status'] == $status
            ) {
                return $this->parseNotification($notification, $deploy);
            }
        }

        return null;
    }

    /**
     * @param string|null $oldStatus
     * @param string      $newStatus
     */
    protected function getStatus($oldStatus, string $newStatus)
    {
        if (in_array($newStatus, [Deploy::STATUS_DONE, Deploy::STATUS_CANCELED])) {
            return $newStatus;
        }

        if (in_array($oldStatus, [null, Deploy::STATUS_NEW, Deploy::STATUS_QUEUED])
            && in_array($newStatus, [Deploy::STATUS_MERGE, Deploy::STATUS_DEPLOY, Deploy::STATUS_WAITING])
        ) {
            return 'new';
        }

        return null;
    }

    /**
     * @param array  $notification
     * @param Deploy $deploy
     */
    protected function parseNotification(array $notification, Deploy $deploy)
    {
        if (!empty($notification['message'])) {
            $search = [
                '{user_name}',
                '{user_login}',
                '{owner}',
                '{repository}',
                '{description}',
            ];

            $replace = [
                ($user = $deploy->getUser())->getName(),
                $user->getLogin(),
                $deploy->getOwner(),
                $deploy->getRepository(),
                $deploy->getDescription(),
            ];

            $notification['message'] = str_replace($search, $replace, $notification['message']);
        }

        return $notification;
    }
}
