<?php

namespace Inquisition\Core\Domain\Event;

use DateTimeImmutable;

/**
 * Domain Event Interface
 * Defines the contract for domain events
 */
interface DomainEventInterface
{
    /**
     * Get when the event occurred
     */
    public function getOccurredOn(): DateTimeImmutable;

    /**
     * Get the aggregate ID that triggered this event
     */
    public function getAggregateId(): string;

    /**
     * Get event name/type
     */
    public function getEventName(): string;

    /**
     * Get an event version for evolution compatibility
     */
    public function getEventVersion(): int;

    /**
     * Get event payload/data
     */
    public function getEventData(): array;

    /**
     * Get event metadata (user, correlation ID, etc.)
     */
    public function getMetadata(): array;

    /**
     * Convert event to array for serialization
     */
    public function toArray(): array;

    /**
     * Create an event from an array (deserialization)
     */
    public static function fromArray(array $data): static;
}
