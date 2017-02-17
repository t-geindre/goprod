<?php

namespace ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use ApiBundle\Validator\Constraints as ApiAssert;

/**
 * Deploy
 *
 * @ORM\Table(name="deploy")
 * @ORM\Entity()
 * @ApiAssert\UniqNotDone
 */
class Deploy
{
    const STATUS_NEW = 'new';
    const STATUS_QUEUED = 'queued';
    const STATUS_MERGE = 'merge';
    const STATUS_DEPLOY = 'deploy';
    const STATUS_WAITING = 'waiting';
    const STATUS_DONE = 'done';
    const STATUS_CANCELED = 'canceled';

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
     * @ORM\Column(name="owner", type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="repository", type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $repository;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     * @Assert\NotBlank()
     */
    protected $description;

    /**
     * @var int
     *
     * @ORM\Column(name="pull_request", type="integer", nullable=true)
     */
    protected $pullRequestId;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=50)
     * @Assert\NotBlank()
     */
    protected $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetime")
     */
    protected $createDate;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="deploys")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->status = self::STATUS_NEW;
        $this->createDate = new \DateTime;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set owner
     *
     * @param string $owner
     *
     * @return Deploy
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set repository
     *
     * @param string $repository
     *
     * @return Deploy
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Get repository
     *
     * @return string
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Deploy
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set pullRequestId
     *
     * @param integer $pullRequestId
     *
     * @return Deploy
     */
    public function setPullRequestId($pullRequestId)
    {
        $this->pullRequestId = $pullRequestId;

        return $this;
    }

    /**
     * Get pullRequestId
     *
     * @return integer
     */
    public function getPullRequestId()
    {
        return $this->pullRequestId;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Deploy
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set user
     *
     * @param \ApiBundle\Entity\User $user
     *
     * @return Deploy
     */
    public function setUser(\ApiBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \ApiBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set createDate
     *
     * @param \DateTime $createDate
     *
     * @return Deploy
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
