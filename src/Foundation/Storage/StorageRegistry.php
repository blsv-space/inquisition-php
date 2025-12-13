<?php

namespace Inquisition\Foundation\Storage;

use Inquisition\Foundation\Config\Config;
use Inquisition\Foundation\Singleton\SingletonInterface;
use Inquisition\Foundation\Singleton\SingletonTrait;
use InvalidArgumentException;

class StorageRegistry implements SingletonInterface
{
    use SingletonTrait;

    /**
     * @param array<string, StorageInterface> $storages
     */
    protected function __construct(private array $storages)
    {
    }

    /**
     * @param string $name
     * @return StorageInterface
     */
    public function storage(string $name = 'default'): StorageInterface
    {
        if (!isset($this->storages[$name])) {
            throw new InvalidArgumentException('Unknown storage provider: ' . $name);
        }

        return $this->storages[$name];
    }

    /**
     * @param string $name
     * @return void
     */
    protected function createFromConfig(string $name): void
    {
        $storageConfig = Config::getInstance()->getByPath('storages.' . $name);
        if ($storageConfig === null) {
            return;
        }

        if (!is_array($storageConfig)
        ) {
            throw new InvalidArgumentException('Invalid storage configuration: ' . $name);
        }

        $this->validateConfig($storageConfig);

        $options = [];

        if (!empty($storageConfig['options'])
            && is_array($storageConfig['options'])
        ) {
            $options = $storageConfig['options'];
        }

        $this->storages[$name] = new $storageConfig['storage'](
            rootPath: $storageConfig['rootPath'],
            options: $options
        );
    }

    /**
     * @param array $storageConfig
     * @return void
     */
    protected function validateConfig(array $storageConfig): void
    {
        $errors = [];

        if (!isset($storageConfig['storage'])
            || !class_exists($storageConfig['storage'])) {
            $errors[] = 'Storage class not found';
        }

        if (!is_subclass_of($storageConfig['storage'], StorageInterface::class)) {
            $errors[] = 'Storage class must implement StorageInterface';
        }

        if (!isset($storageConfig['rootPath'])) {
            $errors[] = 'Storage root path not specified';
        }

        if (!empty($errors)) {
            throw new InvalidArgumentException('Invalid storage configuration: ' . implode(', ', $errors));
        }
    }
}