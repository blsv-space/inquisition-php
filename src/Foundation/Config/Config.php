<?php

namespace Inquisition\Foundation\Config;

use Inquisition\Foundation\Singleton\SingletonTrait;

final class Config implements ConfigInterface
{
    use SingletonTrait;

    private array $config = [];

    /**
     * @param array $config
     * @return void
     */
    public function load(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @param string $prefix
     * @param bool $merge
     * @return void
     */
    public function loadFromEnvironment(string $prefix = '', bool $merge = true): void
    {
        $envConfig = [];

        foreach ($_ENV as $key => $value) {
            if ($prefix && !str_starts_with($key, $prefix)) {
                continue;
            }

            $configKey = $prefix ? substr($key, strlen($prefix)) : $key;
            $configKey = strtolower(str_replace('_', '.', $configKey));
            $parsedValue = $this->parseEnvironmentValue($value);
            $this->setByPath($envConfig, $configKey, $parsedValue);
        }

        if ($merge) {
            $this->config = array_merge_recursive($this->config, $envConfig);
        } else {
            $this->config = $envConfig;
        }
    }

    /**
     * @param string $value
     * @return mixed
     */
    private function parseEnvironmentValue(string $value): mixed
    {
        $value = strtolower($value);

        if (in_array($value, ['true', '1', 'yes', 'on'])) {
            return true;
        }
        if (in_array($value, ['false', '0', 'no', 'off'])) {
            return false;
        }

        if ($value === 'null') {
            return null;
        }

        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        if (str_starts_with($value, '{') || str_starts_with($value, '[')) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $value;
    }

    /**
     * @param array $array
     * @param string $path
     * @param mixed $value
     * @return void
     */
    private function setByPath(array &$array, string $path, mixed $value): void
    {
        $keys = explode('.', $path);
        $current = &$array;

        foreach ($keys as $key) {
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }

        $current = $value;
    }

    /**
     * @param string $path
     * @param $default
     * @return array|mixed|null
     */
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

    /**
     * @param string $key
     * @param $default
     * @return mixed|null
     */
    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param $value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->config[$key] = $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->config[$key]);
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->config;
    }
}
