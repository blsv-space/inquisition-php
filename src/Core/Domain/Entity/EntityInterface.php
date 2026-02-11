<?php

declare(strict_types=1);

namespace Inquisition\Core\Domain\Entity;

use Inquisition\Core\Domain\ValueObject\AbstractValueObject;

interface EntityInterface
{
    /**
     * Check if this entity is equal to another entity
     * Equality is based on ID and type, not object reference
     */
    public function equals(EntityInterface $other): bool;

    /**
     * Get the entity type/class name
     * Useful for equality checks and debugging
     */
    public function getEntityType(): string;

    /**
     * @return AbstractValueObject[]
     */
    public function getAsArray(): array;

}
