<?php

declare(strict_types=1);

namespace Inquisition\Core\Domain\Entity;

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
     * @psalm-return array<string, null|scalar>
     */
    public function getAsArray(): array;

}
