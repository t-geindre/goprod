<?php

namespace ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use ApiBundle\Validator\Constraints as ApiAssert;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity()
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
     * @ORM\Column(name="golive_key", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"complete_profile"})
     * @ApiAssert\GoliveApiKey(groups={"complete_profile"})
     */
    protected $goliveKey;

    /**
     * @var string
     *
     * @ORM\Column(name="access_token", type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $accessToken;

    /**
     * @var string
     *
     * @ORM\Column(name="avatar_url", type="string", length=255)
     */
    protected $avatarUrl;

    /**
     * @ORM\OneToMany(targetEntity="Deploy", mappedBy="user")
     */
    private $deploys;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetime")
     */
    protected $createDate;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->deploys = new \Doctrine\Common\Collections\ArrayCollection();
        $this->createDate = new \DateTime();
    }

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
        if (empty($goliveKey)) {
            return;
        }

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

    /**
     * Set avatarUrl
     *
     * @param string $avatarUrl
     *
     * @return User
     */
    public function setAvatarUrl($avatarUrl)
    {
        $this->avatarUrl = $avatarUrl;

        return $this;
    }

    /**
     * Get avatarUrl
     *
     * @return string
     */
    public function getAvatarUrl()
    {
        return $this->avatarUrl;
    }

    /**
     * Add deploy
     *
     * @param \ApiBundle\Entity\Deploy $deploy
     *
     * @return User
     */
    public function addDeploy(\ApiBundle\Entity\Deploy $deploy)
    {
        $this->deploys[] = $deploy;

        return $this;
    }

    /**
     * Remove deploy
     *
     * @param \ApiBundle\Entity\Deploy $deploy
     */
    public function removeDeploy(\ApiBundle\Entity\Deploy $deploy)
    {
        $this->deploys->removeElement($deploy);
    }

    /**
     * Get deploys
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDeploys()
    {
        return $this->deploys;
    }

    /**
     * Set createDate
     *
     * @param \DateTime $createDate
     *
     * @return User
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }
}
