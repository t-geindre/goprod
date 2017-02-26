<?php
namespace ApiBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use ApiBundle\Service\Golive\Client;
use ApiBundle\Service\Golive\Exception\AuthFailException;

/**
 * Valid Api Key
 */
class GoliveApiKeyValidator extends ConstraintValidator
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @param Client $client
     *
     * @return GoliveApiKeyValidator
     */
    public function setClient(Client $client) : GoliveApiKeyValidator
    {
        $this->client = $client;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            return;
        }

        try {
            $this->client->setAccessToken($value)->whoAmI();
        } catch (AuthFailException $e) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
