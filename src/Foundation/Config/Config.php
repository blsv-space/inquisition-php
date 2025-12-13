<?php

namespace Inquisition\Foundation\Config;

use Inquisition\Foundation\Singleton\SingletonTrait;
use RuntimeException;

final class Config implements ConfigInterface
{
    private const string ENV_CONFIG_WORD_SEPARATOR = '__';
    private const string ENV_CONFIG_WORD_SEPARATOR_TMP = '%%separator%%';

    use SingletonTrait;

    private array $config = [];

    /**
     * @param array $config
     *
     * @return void
     */
    public function load(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @param array $config
     * @param bool|null $override
     *
     * @return void
     */
    public function merge(array $config, ?bool $override = false): void
    {
        if ($override) {
            $this->config = array_merge_recursive($this->config, $config);

            return;
        }
        $this->config = array_merge_recursive($config, $this->config);
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
            if (($prefix && !str_starts_with($key, $prefix))
                || empty($value)
            ) {
                continue;
            }

            $configKey = $prefix ? substr($key, strlen($prefix)) : $key;
            $configKey = str_replace(self::ENV_CONFIG_WORD_SEPARATOR, self::ENV_CONFIG_WORD_SEPARATOR_TMP, $configKey);
            $configKey = strtolower(str_replace('_', '.', $configKey));
            $configKey = str_replace(self::ENV_CONFIG_WORD_SEPARATOR_TMP, '_', $configKey);
            $parsedValue = $this->parseEnvironmentValue($value);
            $this->setByPath($envConfig, $configKey, $parsedValue);
        }

        if ($merge) {
            $this->config = $this->arrayMergeRecursiveOverwrite($this->config, $envConfig);
        } else {
            $this->config = $envConfig;
        }
    }

    /**
     * @param string $path
     * @param bool $override
     * @return void
     */
    public function loadEnvFromFile(string $path, bool $override = false): void
    {
        if (!is_file($path)) {
            throw new RuntimeException("Environment file not found: {$path}");
        }

        $data = parse_ini_file($path, false, INI_SCANNER_RAW);

        if ($data === false) {
            throw new RuntimeException("Failed to parse environment file: {$path}");
        }

        foreach ($data as $key => $value) {
            $key = trim($key);
            $value = trim((string)$value);

            if ($key === ''
                || str_starts_with($key, '#')
                || str_starts_with($value, '#')
            ) {
                continue;
            }

            if (!$override && (getenv($key) !== false || isset($_ENV[$key]))) {
                continue;
            }

            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    /**
     * @param array $array1
     * @param array $array2
     * @return array
     */
    private function arrayMergeRecursiveOverwrite(array $array1, array $array2): array
    {
        foreach ($array2 as $key => $value) {
            if (isset($array1[$key]) && is_array($array1[$key]) && is_array($value)) {
                $array1[$key] = $this->arrayMergeRecursiveOverwrite($array1[$key], $value);
            } else {
                $array1[$key] = $value;
            }
        }

        return $array1;
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
            return str_contains($value, '.') ? (float)$value : (int)$value;
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
    public function getByPath(string $path, $default = null): mixed
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
    public function get(string $key, $default = null): mixed
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
