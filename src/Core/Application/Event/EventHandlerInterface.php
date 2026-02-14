<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Event;

/**
 * @template T of EventInterface
 */
interface EventHandlerInterface
{
    /**
     * Handle the domain event
     *
     * @param T $event
     */
    public function handle(EventInterface $event): void;

    /**
     * Get the event types this handler can process
     * @return array<class-string<T>>
     */
    public function getHandledEvents(): array;

}
