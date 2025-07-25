<?php

namespace Inquisition\Foundation\Singleton;

use LogicException;

trait SingletonTrait
{
    private static ?self $instance = null;

    /**
     *
     */
    private function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function __clone()
    {
        throw new LogicException('Cannot clone singleton');
    }

    /**
     * @return mixed
     */
    public function __wakeup()
    {
        throw new LogicException('Cannot unserialize singleton');
    }

    public static function getInstance(): self
    {
        if (static::$instance === null) {
            static::$instance = new static();
            if (static::class !== SingletonRegistry::class) {
                SingletonRegistry::getInstance()->register(static::class);
            }
        }

        return static::$instance;
    }

    /**
     * @return bool
     */
    public static function hasInstance(): bool
    {
        return static::$instance !== null;
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
