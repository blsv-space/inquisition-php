<?php

namespace Inquisition\Foundation\Singleton;

interface SingletonInterface
{
    public static function getInstance(): static;

    public static function reset(): void;

    public static function hasInstance(): bool;

    public static function override(?self $instance = null): void;
}