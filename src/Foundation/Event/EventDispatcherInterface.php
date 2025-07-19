<?php

namespace Inquisition\Foundation\Event;

interface EventDispatcherInterface
{
    public function dispatch(object $event): void;

    public function listen(string $eventClass, callable $listener): void;
}
