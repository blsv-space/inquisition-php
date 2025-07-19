<?php

namespace Inquisition\Foundation\Config;

use Inquisition\Foundation\Singleton\SingletonTrait;

final class Config implements ConfigInterface
{
    use SingletonTrait;
    private array $config = [];

    public function load(array $config): void
    {
        $this->config = $config;
    }

    public function getByPath(string $path, $default = null)
    {
        $path = explode('.', $path);
        $value = $this->config;
        foreach ($path as $key) {
            if (!isset($value[$key])) {
                return $default;
            }
            $value = $value[$key];
        }

        return $value;
    }

    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $this->config[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($this->config[$key]);
    }

    public function all(): array
    {
        return $this->config;
    }
}
