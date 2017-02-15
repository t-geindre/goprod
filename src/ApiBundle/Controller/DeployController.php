<?php

namespace ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\Criteria;
use ApiBundle\Entity\Deploy;

class DeployController extends BaseController
{
    public function createAction(Request $request)
    {
        return $this->handleForm(
            $request,
            \ApiBundle\Form\DeployType::class,
            (new \ApiBundle\Entity\Deploy)->setUser($this->getUser())
        );
    }

    public function getByCurrentUserAction()
    {
        return $this->getUser()->getDeploys()
            ->matching(
                Criteria::create()
                    ->where(Criteria::expr()->neq('status', Deploy::STATUS_DONE))
                    ->andWhere(Criteria::expr()->neq('status', Deploy::STATUS_CANCELED))
                    ->orderBy(['id' => 'ASC'])
            );
    }
}
