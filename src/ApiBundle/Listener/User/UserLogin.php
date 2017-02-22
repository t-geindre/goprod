<?php

namespace ApiBundle\Listener\User;

use ApiBundle\Service\Github\Event\UserLogin as Event;
use Doctrine\ORM\EntityRepository;
use ApiBundle\Entity\User;
use Doctrine\ORM\EntityManager;

/**
 * Create user on github login
 */
class UserLogin
{
    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param EntityRepository $repository
     * @param EntityManager    $em
     */
    public function __construct(
        EntityRepository $repository,
        EntityManager $em
    ) {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * @param Event $event
     */
    public function onUserLogin(Event $event)
    {
        $githubUser = $event->getClient()->getCurrentUser();
        $response = $event->getResponse();

        if (is_null($user = $this->repository->findOneByLogin($githubUser['login']))) {
            $user = (new User());
        }

        $user
            ->setLogin($githubUser['login'])
            ->setName($githubUser['name'])
            ->setAvatarUrl($githubUser['avatar_url'])
            ->setAccessToken($response['access_token'])
        ;

        $this->em->persist($user);
        $this->em->flush();
    }
}
