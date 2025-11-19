<?php

namespace Inquisition\Core\Domain\Entity;

abstract class BaseEntityWithId extends BaseEntity
    implements EntityWithIdInterface
{
    /**
     * @param EntityInterface $other
     * @return bool
     */
    public function equals(EntityInterface $other): bool
    {
        if (!$other instanceof static) {
            return false;
        }
        if (is_null($this->getId()) || is_null($other->getId())) {
            return false;
        }

        return $this->getId()->equals($other->getId());
    }
}