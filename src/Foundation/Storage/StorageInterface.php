<?php

namespace Inquisition\Foundation\Storage;

use Inquisition\Foundation\Storage\Exception\StorageException;
use SplFileInfo;

interface StorageInterface
{
    public function __construct(
        string                  $rootPath,
        StorageOptionsInterface $options,
    );

    /**
     * Store/overwrite a file at $path with given contents.
     *
     * @param string $path
     * @param string $contents
     * @param StorageWriteOptions|null $options
     * @return void
     * @throws StorageException
     */
    public function writeByPath(string $path, string $contents, ?StorageWriteOptions $options = null): void;

    /**
     * Store/overwrite a file by SplFileInfo object.
     *
     * @param SplFileInfo $fileInfo
     * @param string $contents
     * @param StorageWriteOptions|null $options
     * @return void
     */
    public function write(SplFileInfo $fileInfo, string $contents, ?StorageWriteOptions $options = null): void;

    /**
     * Get a SplFileInfo object for a file at $path.
     *
     * @param string $path
     * @return SplFileInfo|null
     */
    public function get(string $path): ?SplFileInfo;

    /**
     * Read file contents from $path.
     *
     * @param string $path
     * @return string
     * @throws StorageException
     */
    public function readByPath(string $path): string;

    /**
     * Read file contents from the SplFileInfo object.
     *
     * @param SplFileInfo $fileInfo
     * @return string
     * @throws StorageException
     */
    public function read(SplFileInfo $fileInfo): string;

    /**
     * Delete a file (no-op if it doesn't exist is acceptable).
     *
     * @param string $path
     * @throws StorageException
     */
    public function deleteByPath(string $path): void;

    /**
     * Delete a file by SplFileInfo object.
     *
     * @param SplFileInfo $fileInfo
     * @return void
     * @throws StorageException
     */
    public function delete(SplFileInfo $fileInfo): void;

    /**
     * Delete a directory (no-op if it doesn't exist is acceptable).
     *
     * @param string $dir
     * @throws StorageException
     */
    public function deleteDirectoryByPath(string $dir): void;

    /**
     * Delete a directory by SplFileInfo object.
     *
     * @param SplFileInfo $dir
     * @return void
     * @throws StorageException
     */
    public function deleteDirectory(SplFileInfo $dir): void;

    /**
     * Check if a file exists.
     *
     * @param string $path
     * @return bool
     * @throws StorageException
     */
    public function fileExists(string $path): bool;

    /**
     * Check if a directory exists.
     *
     * @param string $path
     * @return bool
     * @throws StorageException
     */
    public function dirExists(string $path): bool;

    /**
     * List files and directories under a path.
     *
     * @param string $path
     * @param bool $recursively
     * @param StorageListTypeEnum $type
     * @return SplFileInfo[]
     * @throws StorageException
     */
    public function list(
        string $path,
        bool $recursively = false,
        StorageListTypeEnum $type = StorageListTypeEnum::All
    ): array;

    /**
     * List files under a path (directory-like).
     *
     * @param string $path
     * @param bool $recursively
     * @return SplFileInfo[]
     * @throws StorageException
     */
    public function listFiles(string $path = '', bool $recursively = false): array;

    /**
     * List directories under a path (directory-like).
     *
     * @param string $path
     * @param bool $recursively
     * @return SplFileInfo[]
     * @throws StorageException
     */
    public function listDirs(string $path = '', bool $recursively = false): array;

}