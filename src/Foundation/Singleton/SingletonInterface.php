<?php

namespace Inquisition\Foundation\Singleton;

interface SingletonInterface
{
    public static function getInstance(): self;

    public static function reset(): void;

    public static function hasInstance(): bool;
}