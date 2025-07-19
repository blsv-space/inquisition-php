<?php

namespace Inquisition\Foundation\Config;

use Inquisition\Foundation\Singleton\SingletonInterface;

interface ConfigInterface extends SingletonInterface
{

    public function load(array $config);

    public function get(string $key, $default = null);

    public function getByPath(string $path, $default = null);

    public function set(string $key, $value): void;

    public function has(string $key): bool;

    public function all(): array;

}