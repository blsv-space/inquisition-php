<?php

declare(strict_types=1);

namespace Inquisition\Foundation\Storage;

use Inquisition\Foundation\Config\Config;
use Inquisition\Foundation\Singleton\SingletonInterface;
use Inquisition\Foundation\Singleton\SingletonTrait;
use InvalidArgumentException;

class StorageRegistry implements SingletonInterface
{
    use SingletonTrait;
    public const string NAME_LOCAL = 'local';
    public const string NAME_DEFAULT = 'default';

    /**
     * @var array<string, StorageInterface> $storages
     */
    private array $storages = [];

    public function storage(string $name = self::NAME_DEFAULT): StorageInterface
    {
        if ($name === self::NAME_DEFAULT) {
            $name = Config::getInstance()->getByPath('storages.default') ?: self::NAME_LOCAL;
        }

        if (!isset($this->storages[$name])) {
            $this->createFromConfig($name);
        }

        if (!isset($this->storages[$name])) {
            throw new InvalidArgumentException('Unknown storage provider: ' . $name);
        }

        return $this->storages[$name];
    }

    protected function createFromConfig(string $name): void
    {
        $storageConfig = Config::getInstance()->getByPath('storage.providers.' . $name);
        if ($storageConfig === null) {
            return;
        }

        if (!is_array($storageConfig)
        ) {
            throw new InvalidArgumentException('Invalid storage configuration: ' . $name);
        }

        $this->validateConfig($storageConfig);

        $options = null;

        if (isset($storageConfig['options'])) {
            $options = $storageConfig['options'];
        }

        $this->storages[$name] = new $storageConfig['provider'](
            rootPath: $storageConfig['root_path'],
            options: $options,
        );
    }

    protected function validateConfig(array $storageConfig): void
    {
        $errors = [];

        if (!isset($storageConfig['provider'])
            || !class_exists($storageConfig['provider'])) {
            $errors[] = 'Storage class not found: ';
        }

        if (!is_subclass_of($storageConfig['provider'], StorageInterface::class)) {
            $errors[] = 'Storage class must implement StorageInterface';
        }

        if (!isset($storageConfig['root_path'])) {
            $errors[] = 'Storage root path not specified';
        }

        if (isset($storageConfig['options'])
            && !is_subclass_of($storageConfig['options'], StorageOptionsInterface::class)
        ) {
            $errors[] = 'Storage options must implement: ' . StorageOptionsInterface::class;
        }

        if (!empty($errors)) {
            throw new InvalidArgumentException('Invalid storage configuration: ' . implode(', ', $errors));
        }
    }
}
