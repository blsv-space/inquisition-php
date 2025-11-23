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
     * Get event name/type
     */
    public function getEventName(): string;

}