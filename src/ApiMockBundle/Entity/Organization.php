<?php

namespace ApiMockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Organization
 *
 * @ORM\Table(name="mock_organization")
 * @ORM\Entity
 */
class Organization extends AbstractEntity
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
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
