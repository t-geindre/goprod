<?php

namespace ApiMockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Base mock
 *
 * @ORM\MappedSuperclass
 */
abstract class AbstractEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="payload", type="text")
     */
    protected $payload;

    /**
     * Set payload
     *
     * @param string $payload
     *
     * @return Mock
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * Get payload
     *
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }
}

