<?php

declare(strict_types=1);

namespace Inquisition\Core\Domain\Entity;

abstract class BaseEntityWithId extends BaseEntity implements EntityWithIdInterface
{
    #[\Override]
    public function equals(EntityInterface $other): bool
    {
        if (!$other instanceof static) {
            return false;
        }

        if (is_subclass_of($this, EntityWithIdInterface::class)
            && is_subclass_of($other, EntityWithIdInterface::class)
        ) {
            return $this->getId()->equals($other->getId());
        }

        return $this === $other;
    }
}
