<?php

namespace Inquisition\Core\Domain\Entity;

interface AggregateRootInterface
{
    /**
     * Get domain events that occurred during this aggregate's lifecycle
     * @return array
     */
    public function getDomainEvents(): array;

    /**
     * Clear all domain events (typically after they've been published)
     */
    public function clearDomainEvents(): void;

    /**
     * Get the aggregate version for optimistic locking
     */
    public function getVersion(): int;

}