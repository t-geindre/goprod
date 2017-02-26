<?php

namespace ApiBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class GoliveApiKey extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public $message = 'This API key is not valid.';
}
