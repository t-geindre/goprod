<?php

namespace ApiBundle\Criteria;

use Doctrine\Common\Collections\Criteria;

/**
 * Base criteria
 */
abstract class AbstractCriteria
{
    /**
     * @return Criteria
     */
    abstract protected function build() : Criteria;
}
