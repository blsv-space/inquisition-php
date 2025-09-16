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
        if (is_null($this->id) || is_null($other->id)) {
            return false;
        }

        return $this->id === $other->id;
    }
}