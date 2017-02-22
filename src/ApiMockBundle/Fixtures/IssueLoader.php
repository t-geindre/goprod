<?php

namespace ApiMockBundle\Fixtures;

use ApiMockBundle\Entity\Issue;

/**
 * Load issues fixtures
 */
class IssueLoader extends AbstractLoader
{
    /**
     * @var array|null
     */
    protected $users = null;

    protected function getEntityClass() : string
    {
        return 'ApiMockBundle\\Entity\\Issue';
    }

    protected function getUsers()
    {
        if (is_null($this->users)) {
            $this->users = $this
                ->manager
                ->getRepository('ApiMockBundle\\Entity\\User')->findAll();
        }

        return $this->users;
    }

    protected function localPopulateEntity($entity, array $payload)
    {
        $parts = explode('/', $payload["repository_url"]);
        $entity->setRepo(array_pop($parts));
        $entity->setOwner(array_pop($parts));
        $entity->setNumber($payload['number']);
        $entity->setAuthor($payload['user']['login']);
        $entity->setOpen($payload['state'] == 'open');
    }

    protected function populateEntity($entity, array $payload)
    {
        $class = $this->getEntityClass();
        $userDone = $payload['user']['login'];
        $this->localPopulateEntity($entity, $payload);
        $count = 0;

        foreach ($this->getUsers() as $user) {
            if ($user->getPayload()['login'] == $userDone) {
                continue;
            }
            $issue = (new $class())->setPayload(
                $payload = array_merge(
                    $payload,
                    [
                        'user' => [
                            'login' => $user->getPayload()['login'],
                            'avatar_url' => $user->getPayload()['avatar_url'],
                        ],
                        'number' => $payload['number'] + $count++,
                    ]
                )
            );
            $this->localPopulateEntity($issue, $payload);
            $this->save($issue);
        }
    }
}
