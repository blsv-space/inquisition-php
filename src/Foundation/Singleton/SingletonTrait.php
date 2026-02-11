<?php

declare(strict_types=1);

namespace Inquisition\Foundation\Singleton;

use LogicException;

trait SingletonTrait
{
    protected static ?self $instance = null;

    private function __construct() {}

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

    /**
     * @return mixed
     */
    public function __sleep()
    {
        throw new LogicException('Cannot serialize singleton');
    }

    /**
     * @return mixed
     */
    public function __serialize()
    {
        throw new LogicException('Cannot serialize singleton');
    }

    /**
     * @return mixed
     */
    public function __unserialize(array $data)
    {
        throw new LogicException('Cannot unserialize singleton');
    }

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
            if (static::class !== SingletonRegistry::class) {
                SingletonRegistry::getInstance()->register(static::class);
            }
        }

        return static::$instance;
    }

    public static function hasInstance(): bool
    {
        return static::$instance !== null;
    }

    public static function reset(): void
    {
        static::$instance = null;
    }

    public static function override(?SingletonInterface $instance = null): void
    {
        if (!is_null($instance)
                && static::class !== $instance::class
        ) {
            throw new LogicException('Cannot override singleton with different class');
        }

        if ($instance !== null) {
            static::$instance = $instance;
            return;
        }
        static::$instance = new static();
    }
}
