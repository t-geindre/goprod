<?php

namespace ApiMockBundle\Fixtures;

use ApiMockBundle\Entity\Issue;

/**
 * Load users fixtures
 */
class UserLoader extends AbstractLoader
{
    protected function getEntityClass() : string
    {
        return 'ApiMockBundle\\Entity\\User';
    }

    protected function populateEntity($entity, array $payload)
    {
        $entity->setOauthCode($payload['oauth_code']);
        $entity->setAccessToken($payload['access_token']);
    }
}
