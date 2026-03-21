<?php

declare(strict_types=1);

namespace Inquisition\Foundation\Singleton;

use InvalidArgumentException;

class SingletonRegistry implements SingletonInterface
{
    use SingletonTrait;

    private array $singletons = [];

    public function register(string $name): void
    {
        if (array_key_exists($name, $this->singletons)) {
            return;
        }

        if (!is_subclass_of($name, SingletonInterface::class)) {
            throw new InvalidArgumentException("Class $name is not a singleton");
        }

        $this->singletons[$name] = true;
    }

    /**
     * @return list<class-string<SingletonInterface>>
     */
    public function getRegisteredSingletons(): array
    {
        return $this->singletons;
    }

    /**
     * @param array $exclusions - class names to exclude from resetting
     */
    public function resetAll(array $exclusions = []): void
    {
        foreach ($this->singletons as $singleton => $_) {
            if (in_array($singleton, $exclusions)) {
                continue;
            }
            if (is_subclass_of($singleton, SingletonInterface::class)) {
                $singleton::reset();
            }
        }
        $this->singletons = array_intersect_key($this->singletons, array_flip($exclusions));
    }
}
