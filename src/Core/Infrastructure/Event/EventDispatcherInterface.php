<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Event;

use Inquisition\Core\Application\Event\EventHandlerInterface;
use Inquisition\Core\Application\Event\EventInterface;
use Inquisition\Foundation\Singleton\SingletonInterface;

interface EventDispatcherInterface extends SingletonInterface
{
    public function dispatch(EventInterface $event): void;

    public function registry(EventHandlerInterface $handler): void;
}
