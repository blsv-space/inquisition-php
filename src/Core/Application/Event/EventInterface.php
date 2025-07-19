<?php

namespace Inquisition\Core\Application\Event;

use DateTimeImmutable;

interface EventInterface
{
    /**
     * Get when the event occurred
     */
    public function getOccurredOn(): DateTimeImmutable;

    /**
     * Get the aggregate ID that triggered this event
     */
    public function getAggregateId();

    /**
     * Get event name/type
     */
    public function getEventName(): string;

}