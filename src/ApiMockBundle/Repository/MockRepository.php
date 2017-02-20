<?php

namespace ApiMockBundle\Repository;

use Doctrine\ORM\EntityRepository;

class MockRepository extends EntityRepository
{
    public function getMocks(string $api, string $type)
    {
        return $this->findBy(['api' => $api, 'type' => $type]);
    }
}
