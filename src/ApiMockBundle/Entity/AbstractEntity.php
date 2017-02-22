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
     * @param array $payload
     *
     * @return Mock
     */
    public function setPayload(array $payload)
    {
        $this->payload = json_encode($payload);

        return $this;
    }

    /**
     * Get payload
     *
     * @return array
     */
    public function getPayload()
    {
        return json_decode($this->payload, true);
    }
}
