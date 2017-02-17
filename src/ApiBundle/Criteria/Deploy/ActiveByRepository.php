<?php

namespace ApiBundle\Criteria\Deploy;

use Doctrine\Common\Collections\Criteria;

class ActiveByRepository extends Active
{
    protected $owner;

    protected $repository;

    public function __construct(string $owner, string $repository)
    {
        $this->owner = $owner;
        $this->repository = $repository;
    }

    public function build()
    {
        return parent::build()
            ->andWhere(Criteria::expr()->eq('owner', $this->owner))
            ->andWhere(Criteria::expr()->eq('repository', $this->repository));
    }
}
