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
     * @param array $exclusions - class names to exclude from resetting
     * @return void
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