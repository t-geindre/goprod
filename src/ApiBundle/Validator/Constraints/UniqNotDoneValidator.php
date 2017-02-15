<?php
namespace ApiBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use ApiBundle\Entity\Deploy;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;

class UniqNotDoneValidator extends ConstraintValidator
{
    protected $repository;

    public function setRepository(EntityRepository $repository)
    {
        $this->repository = $repository;
    }

    public function validate($deploy, Constraint $constraint)
    {
        if (!$deploy instanceof Deploy) {
            throw new \RuntimeException(sprintf('%s only support deploy entity', get_class($this)));
        }

        if (!$this->repository->matching(
            Criteria::create()
                ->where(Criteria::expr()->neq('status', Deploy::STATUS_DONE))
                ->andWhere(Criteria::expr()->neq('status', Deploy::STATUS_CANCELED))
                ->andWhere(Criteria::expr()->eq('user', $deploy->getUser()))
                ->andWhere(Criteria::expr()->eq('owner', $deploy->getOwner()))
                ->andWhere(Criteria::expr()->eq('repository', $deploy->getRepository()))
        )->isEmpty()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
