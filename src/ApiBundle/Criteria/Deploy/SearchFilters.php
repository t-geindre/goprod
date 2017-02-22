<?php

namespace ApiBundle\Criteria\Deploy;

use ApiBundle\Criteria\AbstractCriteria;
use ApiBundle\Entity\Deploy;
use ApiBundle\Entity\User;
use Doctrine\Common\Collections\Criteria;
use ApiBundle\Criteria\Deploy\Active;

/**
 * Deploys search filters
 */
class SearchFilters extends AbstractCriteria
{
    /**
     * @var string
     */
    protected $status = null;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @param string|null $status
     * @param string|null $owner
     * @param string|null $repository
     * @param User|null   $user
     */
    public function __construct(
        string $status = null,
        string $owner = null,
        string $repository = null,
        User $user = null
    ) {
        $this->status = $status;
        $this->fields = [
            'owner' => $owner,
            'repository' => $repository,
            'user' => $user,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function build() : Criteria
    {
        if ($this->status == 'active') {
            $criteria = (new Active())->build();
        } else {
            $criteria = Criteria::create();
            if (!empty($this->status)
                && !in_array($this->status, [Deploy::STATUS_CANCELED, Deploy::STATUS_DONE])
            ) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid status value "%s"',
                    $this->status
                ));
            }
            if (!empty($this->status)) {
                $criteria->andWhere(Criteria::expr()->eq('status', $this->status));
            }
        }

        foreach ($this->fields as $field => $val) {
            if (!empty($val)) {
                $criteria->andWhere(Criteria::expr()->eq($field, $val));
            }
        }

        return $criteria;
    }
}
