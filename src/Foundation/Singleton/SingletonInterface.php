<?php

declare(strict_types=1);

namespace Inquisition\Foundation\Singleton;

interface SingletonInterface
{
    public static function getInstance(): static;

    public static function reset(): void;

    public static function hasInstance(): bool;

    /**
     * @param static|null $instance
     */
    public static function override(?SingletonInterface $instance = null): void;
}
