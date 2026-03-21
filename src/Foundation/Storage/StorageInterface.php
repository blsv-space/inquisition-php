<?php

declare(strict_types=1);

namespace Inquisition\Foundation\Storage;

use Inquisition\Foundation\Storage\Exception\StorageException;
use SplFileInfo;

/**
 * @template T of StorageOptionsInterface
 */
interface StorageInterface
{
    public function __construct(
        string                  $rootPath,
        /**
         * @var T|null $options
         */
        ?StorageOptionsInterface $options = null,
    );

    /**
     * Store/overwrite a file at $path with given contents.
     *
     * @throws StorageException
     */
    public function writeByPath(string $path, string $contents, ?StorageWriteOptions $options = null): void;

    /**
     * Store/overwrite a file by SplFileInfo object.
     *
     */
    public function write(SplFileInfo $fileInfo, string $contents, ?StorageWriteOptions $options = null): void;

    /**
     * Get a SplFileInfo object for a file at $path.
     *
     */
    public function get(string $path): ?SplFileInfo;

    /**
     * Read file contents from $path.
     *
     * @throws StorageException
     */
    public function readByPath(string $path): string;

    /**
     * Read file contents from the SplFileInfo object.
     *
     * @throws StorageException
     */
    public function read(SplFileInfo $fileInfo): string;

    /**
     * Delete a file (no-op if it doesn't exist is acceptable).
     *
     * @throws StorageException
     */
    public function deleteByPath(string $path): void;

    /**
     * Delete a file by SplFileInfo object.
     *
     * @throws StorageException
     */
    public function delete(SplFileInfo $fileInfo): void;

    /**
     * Delete a directory (no-op if it doesn't exist is acceptable).
     *
     * @throws StorageException
     */
    public function deleteDirectoryByPath(string $dir): void;

    /**
     * Delete a directory by SplFileInfo object.
     *
     * @throws StorageException
     */
    public function deleteDirectory(SplFileInfo $dir): void;

    /**
     * Check if a file exists.
     *
     * @throws StorageException
     */
    public function fileExists(string $path): bool;

    /**
     * Check if a directory exists.
     *
     * @throws StorageException
     */
    public function dirExists(string $path): bool;

    /**
     * List files and directories under a path.
     *
     * @throws StorageException
     * @return list<SplFileInfo>
     */
    public function list(
        string $path,
        bool $recursively = false,
        StorageListTypeEnum $type = StorageListTypeEnum::All,
    ): array;

    /**
     * List files under a path (directory-like).
     *
     * @throws StorageException
     * @return list<SplFileInfo>
     */
    public function listFiles(string $path = '', bool $recursively = false): array;

    /**
     * List directories under a path (directory-like).
     *
     * @throws StorageException
     * @return list<SplFileInfo>
     */
    public function listDirs(string $path = '', bool $recursively = false): array;

}
