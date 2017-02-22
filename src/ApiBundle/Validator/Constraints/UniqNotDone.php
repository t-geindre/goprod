<?php

namespace ApiBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqNotDone extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public $message = 'You already created a deployment for this repository. Cancel of finish this other deployment before creating a new one.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
