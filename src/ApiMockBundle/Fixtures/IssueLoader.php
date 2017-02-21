<?php

namespace ApiMockBundle\Fixtures;

use ApiMockBundle\Entity\Issue;

class IssueLoader extends AbstractLoader
{
    protected function getEntityClass() : string
    {
        return 'ApiMockBundle\\Entity\\Issue';
    }

    protected function populateEntity($entity, array $payload)
    {
        $parts = explode('/', $payload["repository_url"]);
        $entity->setRepo(array_pop($parts));
        $entity->setOwner(array_pop($parts));
        $entity->setNumber($payload['number']);
        $entity->setAuthor($payload['user']['login']);
        $entity->setOpen($payload['state'] == 'open');
    }
}
