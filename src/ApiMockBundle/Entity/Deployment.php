<?php

namespace ApiMockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Deployment
 *
 * @ORM\Table(name="mock_deployment")
 * @ORM\Entity
 */
class Deployment
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
     * @ORM\Column(name="project", type="string", length=255)
     */
    protected $project;

    /**
     * @var string
     *
     * @ORM\Column(name="stage", type="string", length=255)
     */
    protected $stage;

    /**
     * @var string
     *
     * @ORM\Column(name="instance", type="string", length=255)
     */
    protected $instance;

    /**
     * @var string
     *
     * @ORM\Column(name="user", type="string", length=255)
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=255)
     */
    protected $action;

    /**
     * @var string
     *
     * @ORM\Column(name="ref", type="string", length=255)
     */
    protected $ref;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(name="revision", type="string", length=255)
     */
    protected $revision;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="client_ip", type="string", length=255)
     */
    protected $clientIp;


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
     * Set project
     *
     * @param string $project
     *
     * @return Deployment
     */
    public function setProject($project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return string
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set stage
     *
     * @param string $stage
     *
     * @return Deployment
     */
    public function setStage($stage)
    {
        $this->stage = $stage;

        return $this;
    }

    /**
     * Get stage
     *
     * @return string
     */
    public function getStage()
    {
        return $this->stage;
    }

    /**
     * Set instance
     *
     * @param string $instance
     *
     * @return Deployment
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;

        return $this;
    }

    /**
     * Get instance
     *
     * @return string
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * Set user
     *
     * @param string $user
     *
     * @return Deployment
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set action
     *
     * @param string $action
     *
     * @return Deployment
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set ref
     *
     * @param string $ref
     *
     * @return Deployment
     */
    public function setRef($ref)
    {
        $this->ref = $ref;

        return $this;
    }

    /**
     * Get ref
     *
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Deployment
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
     * Set revision
     *
     * @param string $revision
     *
     * @return Deployment
     */
    public function setRevision($revision)
    {
        $this->revision = $revision;

        return $this;
    }

    /**
     * Get revision
     *
     * @return string
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Deployment
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
     * Set clientIp
     *
     * @param string $clientIp
     *
     * @return Deployment
     */
    public function setClientIp($clientIp)
    {
        $this->clientIp = $clientIp;

        return $this;
    }

    /**
     * Get clientIp
     *
     * @return string
     */
    public function getClientIp()
    {
        return $this->clientIp;
    }
}
