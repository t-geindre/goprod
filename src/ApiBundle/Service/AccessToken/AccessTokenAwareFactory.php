<?php

namespace ApiBundle\Service\AccessToken;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Tokenify services
 */
class AccessTokenAwareFactory
{

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param AccessTokenAwareInterface $service
     * @param string                    $property
     * @param bool                      $required
     *
     * @return AccessTokenAwareInterface
     */
    public function tokenify(AccessTokenAwareInterface $service, string $property, bool $required) : AccessTokenAwareInterface
    {
        if ($user = $this->tokenStorage->getToken()->getUser()) {
            $service->setAccessToken(
                PropertyAccess::createPropertyAccessor()->getValue($user, $property)
            );

            return $service;
        }

        if ($required) {
            throw new \RuntimeException('Unable to tokenify service, no user found.');
        }

        return $service;
    }
}
