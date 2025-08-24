<?php

namespace Inquisition\Foundation\Singleton;

class SingletonRegistry implements SingletonInterface
{
    use SingletonTrait;

    /**
     * @var array
     */
    private array $singletons = [];

    /**
     * @param class-string<SingletonInterface> $name
     * @return void
     */
    public function register(string $name): void
    {
        if (array_key_exists($name, $this->singletons)) {
            return;
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
     * @return void
     */
    public function resetAll(): void
    {
        foreach ($this->singletons as $singleton => $_) {
            if ($singleton instanceof SingletonInterface) {
                $singleton::reset();
            }
        }
        $this->singletons = [];
    }
}