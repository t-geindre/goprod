<?php

namespace ApiBundle\Service\AccessToken;

/**
 * Enable token auto injection
 */
interface AccessTokenAwareInterface
{
    /**
     * @param string $token
     */
    public function setAccessToken(string $token);
}
