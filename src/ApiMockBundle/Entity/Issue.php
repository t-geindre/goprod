<?php

namespace ApiMockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Issue
 *
 * @ORM\Table(name="mock_issue")
 * @ORM\Entity
 */
class Issue extends AbstractEntity
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
     * @var int
     *
     * @ORM\Column(name="number", type="integer")
     */
    protected $number;

    /**
     * @var string
     *
     * @ORM\Column(name="repo", type="string", length=255)
     */
    protected $repo;

    /**
     * @var string
     *
     * @ORM\Column(name="owner", type="string", length=255)
     */
    protected $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="author", type="string", length=255)
     */
    protected $author;

    /**
     * @var bool
     *
     * @ORM\Column(name="open", type="boolean")
     */
    protected $open;


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
     * Set number
     *
     * @param integer $number
     *
     * @return Issue
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set repo
     *
     * @param string $repo
     *
     * @return Issue
     */
    public function setRepo($repo)
    {
        $this->repo = $repo;

        return $this;
    }

    /**
     * Get repo
     *
     * @return string
     */
    public function getRepo()
    {
        return $this->repo;
    }

    /**
     * Set owner
     *
     * @param string $owner
     *
     * @return Issue
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
     * Set author
     *
     * @param string $author
     *
     * @return Issue
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set open
     *
     * @param boolean $open
     *
     * @return Issue
     */
    public function setOpen($open)
    {
        $this->open = $open;

        return $this;
    }

    /**
     * Get open
     *
     * @return bool
     */
    public function getOpen()
    {
        return $this->open;
    }
}

