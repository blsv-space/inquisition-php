<?php

namespace Inquisition\Foundation\Singleton;

use Inquisition\Foundation\Singleton\SingletonInterface;

class SingletonRegistry implements SingletonInterface
{
    use SingletonTrait;

    /**
     * @var array
     */
    private array $singletons = [];

    /**
     * @param string $name
     * @return void
     */
    public function register(string $name): void
    {
        if (in_array($name, $this->singletons, true)) {
            return;
        }

        $this->singletons[$name] = true;
    }

    /**
     * @return array
     */
    public function getRegisteredSingletons(): array
    {
        return $this->singletons;
    }

    /**
     * @return void
     */
    public function resetAll(): void
    {
        foreach ($this->singletons as $singleton) {
            if ($singleton instanceof SingletonInterface) {
                $singleton::reset();
            }
        }
    }
}