<?php

namespace ApiMockBundle\Fixtures;

use ApiMockBundle\Entity\Issue;

/**
 * Load pullrequests fixtures
 */
class PullrequestLoader extends IssueLoader
{
    protected function getEntityClass() : string
    {
        return 'ApiMockBundle\\Entity\\Pullrequest';
    }

    protected function localPopulateEntity($entity, array $payload)
    {
        $entity->setOwner($payload['head']['user']['login']);
        $entity->setRepo($payload['head']['repo']['name']);
        $entity->setNumber($payload['number']);
        $entity->setAuthor($payload['user']['login']);
        $entity->setOpen($payload['state'] == 'open');
    }
}
