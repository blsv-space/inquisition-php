<?php

namespace Inquisition\Core\Infrastructure\Event;

use Inquisition\Core\Application\Event\EventHandlerInterface;
use Inquisition\Core\Application\Event\EventInterface;
use Inquisition\Foundation\Singleton\SingletonTrait;

final class EventDispatcher implements EventDispatcherInterface
{
    use SingletonTrait;

    /** @var EventHandlerInterface[] */
    private array $handlers = [];

    /**
     * @param EventHandlerInterface $handler
     * @return void
     */
    public function registry(EventHandlerInterface $handler): void
    {
        foreach ($handler->getHandledEvents() as $eventClass) {
            $this->handlers[$eventClass][] = $handler;
        }
    }

    /**
     * @param EventInterface $event
     * @return void
     */
    public function dispatch(EventInterface $event): void
    {
        foreach ($this->handlers[$event::class] ?? [] as $handler) {
            $handler->handle($event);
        }
    }

}