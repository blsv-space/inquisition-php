<?php

namespace Inquisition\Foundation\Singleton;

use LogicException;

trait SingletonTrait
{
    private static ?self $instance = null;

    private function __construct()
    {
    }

    public function __clone()
    {
        throw new LogicException('Cannot clone singleton');
    }

    public function __wakeup()
    {
        throw new LogicException('Cannot unserialize singleton');
    }

    public static function getInstance(): self
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @return void
     */
    public static function reset(): void
    {
        static::$instance = null;
    }

    /**
     * Overrides the current instance with a new static instance of the current Class.
     *
     * @return void
     */
    public static function override(): void
    {
        static::$instance = new static();
    }
}
