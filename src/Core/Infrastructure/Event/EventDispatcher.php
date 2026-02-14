<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Event;

use Inquisition\Core\Application\Event\EventHandlerInterface;
use Inquisition\Core\Application\Event\EventInterface;
use Inquisition\Foundation\Singleton\SingletonTrait;

/**
 * @implements EventDispatcherInterface<EventInterface>
 */
final class EventDispatcher implements EventDispatcherInterface
{
    use SingletonTrait;

    /** @var array<class-string<EventInterface>, EventHandlerInterface[]> */
    private array $handlers = [];

    /**
     * @param EventHandlerInterface<EventInterface> $handler
     */
    #[\Override]
    public function registry(EventHandlerInterface $handler): void
    {
        foreach ($handler->getHandledEvents() as $eventClass) {
            if (!isset($this->handlers[$eventClass])) {
                $this->handlers[$eventClass] = [];
            }
            $this->handlers[$eventClass][] = $handler;
        }
    }

    #[\Override]
    public function dispatch(EventInterface $event): void
    {
        foreach ($this->handlers[$event::class] ?? [] as $handler) {
            $handler->handle($event);
        }
    }

}
