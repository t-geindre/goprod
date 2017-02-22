<?php

namespace ApiBundle\Criteria\Deploy;

use Doctrine\Common\Collections\Criteria;

/**
 * Active deploys for given repository
 */
class ActiveByRepository extends Active
{
    /**
     * @var string
     */
    protected $owner;

    /**
     * @var string
     */
    protected $repository;

    /**
     * @param string $owner
     * @param string $repository
     */
    public function __construct(string $owner, string $repository)
    {
        $this->owner = $owner;
        $this->repository = $repository;
    }

    /**
     * {@inheritDoc}
     */
    public function build() : Criteria
    {
        return parent::build()
            ->andWhere(Criteria::expr()->eq('owner', $this->owner))
            ->andWhere(Criteria::expr()->eq('repository', $this->repository));
    }
}
