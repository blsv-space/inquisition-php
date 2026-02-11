<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Event;

interface EventHandlerInterface
{
    /**
     * Handle the domain event
     */
    public function handle(EventInterface $event): void;

    /**
     * Get the event types this handler can process
     * @return string[]
     */
    public function getHandledEvents(): array;

}
