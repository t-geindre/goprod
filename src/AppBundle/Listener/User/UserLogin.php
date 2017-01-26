<?php

namespace AppBundle\Listener\User;

use AppBundle\Service\Github\Event\UserLogin as Event;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserLogin
{
    protected $repository;
    protected $em;
    protected $validator;

    public function __construct(
        EntityRepository $repository,
        EntityManager $em
    )
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    public function onUserLogin(Event $event)
    {
        $githubUser = $event->getClient()->getCurrentUser();
        $response = $event->getResponse();

        if (is_null($user = $this->repository->findOneByLogin($githubUser['login']))) {
            $user = (new User())
                ->setLogin($githubUser['login'])
                ->setName($githubUser['name']);
        }

        $user->setAccessToken($response['access_token']);

        $this->em->persist($user);
        $this->em->flush();
    }
}
