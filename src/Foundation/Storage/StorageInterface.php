<?php

namespace Inquisition\Foundation\Storage;

use Inquisition\Foundation\Storage\Exception\StorageException;

interface StorageInterface
{
    public function __construct(
        string $rootPath,
        ?array $options = [],
    );

    /**
     * Store/overwrite a file at $path with given contents.
     *
     * @throws StorageException
     */
    public function write(string $path, string $contents, array $options = []): void;

    /**
     * Read file contents from $path.
     *
     * @throws StorageException
     */
    public function read(string $path): string;

    /**
     * Delete a file (no-op if it doesn't exist is acceptable).
     *
     * @throws StorageException
     */
    public function delete(string $path): void;

    /**
     * Check if a file exists.
     *
     * @throws StorageException
     */
    public function exists(string $path): bool;

    /**
     * List files under a prefix (directory-like).
     *
     * @return list<string>
     * @throws StorageException
     */
    public function list(string $prefix = ''): array;
}