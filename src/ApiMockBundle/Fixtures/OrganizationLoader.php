<?php

namespace ApiMockBundle\Fixtures;

use ApiMockBundle\Entity\Issue;

/**
 * Load organizations fixtures
 */
class OrganizationLoader extends AbstractLoader
{
    protected function getEntityClass() : string
    {
        return 'ApiMockBundle\\Entity\\Organization';
    }
}
