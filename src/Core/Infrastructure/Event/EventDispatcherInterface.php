<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Event;

use Inquisition\Core\Application\Event\EventHandlerInterface;
use Inquisition\Core\Application\Event\EventInterface;
use Inquisition\Foundation\Singleton\SingletonInterface;

/**
 * @template T of EventInterface
 */
interface EventDispatcherInterface extends SingletonInterface
{
    /**
     * @param T $event
     */
    public function dispatch(EventInterface $event): void;

    /**
     * @param EventHandlerInterface<T> $handler
     */
    public function registry(EventHandlerInterface $handler): void;
}
