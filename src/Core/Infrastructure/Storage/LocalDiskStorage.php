<?php

namespace Inquisition\Core\Infrastructure\Storage;

use FilesystemIterator;
use Inquisition\Foundation\Storage\Exception\StorageException;
use Inquisition\Foundation\Storage\StorageInterface;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

final readonly class LocalDiskStorage implements StorageInterface
{
    /**
     * @param string $rootPath
     * @param array|null $options
     */
    public function __construct(
        protected string $rootPath,
        protected ?array $options = [],
    )
    {
    }

    /**
     * @return string
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    /**
     * @return array|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * @param string $path
     * @param string $contents
     * @param array $options
     * @return void
     */
    public function write(string $path, string $contents, array $options = []): void
    {
        $fullPath = $this->resolvePath($path);

        $dir = dirname($fullPath);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw StorageException::forWrite($path);
        }

        $bytes = @file_put_contents($fullPath, $contents);
        if ($bytes === false) {
            throw StorageException::forWrite($path);
        }
    }

    /**
     * @param string $path
     * @return string
     */
    public function read(string $path): string
    {
        $fullPath = $this->resolvePath($path);

        $data = @file_get_contents($fullPath);
        if ($data === false) {
            throw StorageException::forRead($path);
        }

        return $data;
    }

    /**
     * @param string $path
     * @return void
     */
    public function delete(string $path): void
    {
        $fullPath = $this->resolvePath($path);

        if (!file_exists($fullPath)) {
            return;
        }

        if (!@unlink($fullPath)) {
            throw StorageException::forDelete($path);
        }
    }

    /**
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        $fullPath = $this->resolvePath($path);

        return file_exists($fullPath);
    }

    /**
     * @param string $prefix
     * @return array|string[]
     */
    public function list(string $prefix = ''): array
    {
        $base = $this->resolvePath($prefix);
        if (!is_dir($base)) {
            return [];
        }

        $files = [];
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $fileInfo) {
                if (!$fileInfo->isFile()) {
                    continue;
                }
                $files[] = $this->toRelativePath($fileInfo->getPathname());
            }
        } catch (Throwable $e) {
            throw StorageException::forList($prefix, $e);
        }

        sort($files);

        return $files;
    }

    /**
     * @param string $path
     * @return string
     */
    private function resolvePath(string $path): string
    {
        $path = str_replace('', '/', $path);
        $path = ltrim($path, '/');

        // Prevent path traversal like ../../secrets
        $parts = [];
        foreach (explode('/', $path) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                throw new InvalidArgumentException('Invalid storage path.');
            }
            $parts[] = $part;
        }

        $safe = implode(DIRECTORY_SEPARATOR, $parts);

        $root = rtrim($this->rootPath, "\\/ \t\n\r\0\x0B");

        return $root . DIRECTORY_SEPARATOR . $safe;
    }

    /**
     * @param string $fullPath
     * @return string
     */
    private function toRelativePath(string $fullPath): string
    {
        $root = rtrim($this->rootPath, "/");

        $fullPathNorm = str_replace('', '/', $fullPath);
        $rootNorm = str_replace('', '/', $root);

        if (str_starts_with($fullPathNorm, $rootNorm . '/')) {
            return substr($fullPathNorm, strlen($rootNorm) + 1);
        }

        return $fullPathNorm;
    }
}