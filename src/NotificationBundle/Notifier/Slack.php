<?php

namespace NotificationBundle\Notifier;

use CL\Slack\Transport\ApiClientInterface as SlackClient;
use CL\Slack\Payload\ChatPostMessagePayload;
use CL\Slack\Model\Attachment;
use ApiBundle\Entity\Deploy;
use ApiBundle\Service\Github\Client as GithubClient;

/**
 * Slack notifier
 */
class Slack extends AbstractNotifier
{
    /**
     * @var SlackClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $icon;

    /**
     * @param array        $notifications
     * @param GithubClient $githubClient
     * @param SlackClient  $client
     * @param string|null  $name
     * @param string|null  $icon
     */
    public function __construct(
        array $notifications,
        GithubClient $githubClient,
        SlackClient $client,
        string $name = null,
        string $icon = null
    ) {
        parent::__construct($notifications, $githubClient);
        $this->client = $client;
        $this->name = $name;
        $this->icon = $icon;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(Deploy $deploy, string $oldStatus = null, string $newStatus)
    {
        $notification = $this->getNotification($deploy, $oldStatus, $newStatus);
        if (is_null($notification)) {
            return;
        }

        $owner = $deploy->getOwner();
        $repository = $deploy->getRepository();

        $payload = new ChatPostMessagePayload();
        $payload->setUsername($this->name);
        $payload->setIconEmoji($this->icon);

        $payload->setChannel($notification['channel']);

        $attachment = new Attachment();
        $attachment->setText($notification['message']);
        $attachment->setMrkdwnIn(['text']);
        $attachment->setTitle($deploy->getDescription());
        $attachment->setColor([
            'new' => 'warning',
            'done' => 'good',
            'canceled' => 'danger',
        ][$notification['status']]);

        $attachment->setAuthorName(sprintf('%s/%s', $owner, $repository));

        if (!is_null($number = $deploy->getPullRequestId())) {
            $pullrequest = $this->githubClient->getPullRequest($owner, $repository, $number);
            $attachment->setAuthorLink($pullrequest['html_url']);
            $attachment->setTitleLink($pullrequest['html_url']);
            $attachment->setAuthorName(sprintf('%s/%s#%d', $owner, $repository, $number));
        }

        $payload->addAttachment($attachment);

        $response = $this->client->send($payload);
    }
}
