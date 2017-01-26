<?php

namespace ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="ApiBundle\Repository\UserRepository")
 */
class User
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
     * @ORM\Column(name="login", type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $login;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="goliveKey", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"complete_profile"})
     */
    protected $goliveKey;

    /**
     * @var string
     *
     * @ORM\Column(name="hipchatName", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"complete_profile"})
     */
    protected $hipchatName;

    /**
     * @var string
     *
     * @ORM\Column(name="accessToken", type="string", length=255)
     * @Assert\NotBlank(groups={"complete_profile"})
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
     * Set login
     *
     * @param string $login
     *
     * @return User
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set goliveKey
     *
     * @param string $goliveKey
     *
     * @return User
     */
    public function setGoliveKey($goliveKey)
    {
        $this->goliveKey = $goliveKey;

        return $this;
    }

    /**
     * Get goliveKey
     *
     * @return string
     */
    public function getGoliveKey()
    {
        return $this->goliveKey;
    }

    /**
     * Set hipchatName
     *
     * @param string $hipchatName
     *
     * @return User
     */
    public function setHipchatName($hipchatName)
    {
        $this->hipchatName = $hipchatName;

        return $this;
    }

    /**
     * Get hipchatName
     *
     * @return string
     */
    public function getHipchatName()
    {
        return $this->hipchatName;
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
