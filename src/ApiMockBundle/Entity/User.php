<?php

namespace ApiMockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="mock_user")
 * @ORM\Entity
 */
class User extends AbstractEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="oauthCode", type="string", length=255)
     */
    protected $oauthCode;

    /**
     * @var string
     *
     * @ORM\Column(name="accessToken", type="string", length=255)
     */
    protected $accessToken;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set oauthCode
     *
     * @param string $oauthCode
     *
     * @return User
     */
    public function setOauthCode($oauthCode)
    {
        $this->oauthCode = $oauthCode;

        return $this;
    }

    /**
     * Get oauthCode
     *
     * @return string
     */
    public function getOauthCode()
    {
        return $this->oauthCode;
    }

    /**
     * Set accessToken
     *
     * @param string $accessToken
     *
     * @return User
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Get accessToken
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }
}
