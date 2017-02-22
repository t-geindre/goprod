<?php

namespace ApiBundle\Criteria\Deploy;

use ApiBundle\Criteria\AbstractCriteria;
use ApiBundle\Entity\Deploy;
use Doctrine\Common\Collections\Criteria;

/**
 * Active deploys
 */
class Active extends AbstractCriteria
{
    /**
     * {@inheritDoc}
     */
    public function build() : Criteria
    {
        return Criteria::create()
            ->where(Criteria::expr()->neq('status', Deploy::STATUS_DONE))
            ->andWhere(Criteria::expr()->neq('status', Deploy::STATUS_CANCELED));
    }
}
