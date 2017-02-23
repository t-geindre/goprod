<?php

namespace ApiMockBundle\Fixtures;

use ApiMockBundle\Entity\Issue;

/**
 * Load repositories fixtures
 */
class RepositoryLoader extends AbstractLoader
{
    protected function getEntityClass() : string
    {
        return 'ApiMockBundle\\Entity\\Repository';
    }

    protected function populateEntity($entity, array $payload)
    {
        $entity->setName($payload['name']);
        $entity->setOwner($payload['owner']['login']);
    }
}
