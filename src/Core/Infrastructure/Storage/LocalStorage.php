<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Storage;

use DirectoryIterator;
use FilesystemIterator;
use Inquisition\Foundation\Kernel;
use Inquisition\Foundation\Storage\Exception\StorageException;
use Inquisition\Foundation\Storage\StorageInterface;
use Inquisition\Foundation\Storage\StorageListTypeEnum;
use Inquisition\Foundation\Storage\StorageOptionsInterface;
use Inquisition\Foundation\Storage\StorageWriteOptions;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

/**
 * @implements StorageInterface<LocalStorageOptions>
 */
final readonly class LocalStorage implements StorageInterface
{
    protected string $rootPath;
    protected LocalStorageOptions $options;

    public function __construct(
        string                            $rootPath,
        ?StorageOptionsInterface          $options = null,
    ) {
        if (is_null($options)) {
            $options = new LocalStorageOptions();
        } elseif (!$options instanceof LocalStorageOptions) {
            throw new InvalidArgumentException('Invalid storage options.');
        }

        $this->options = $options;

        if (empty($rootPath)) {
            throw new InvalidArgumentException('Root path is empty.');
        }
        $rootPath = rtrim(trim($rootPath), '/ \\');
        $rootPath = implode(DIRECTORY_SEPARATOR, explode('/', $rootPath));

        if (!str_starts_with($rootPath, DIRECTORY_SEPARATOR)) {
            $rootPath = Kernel::getInstance()->projectRoot . DIRECTORY_SEPARATOR . $rootPath;
        }
        $this->rootPath = $rootPath;

        if (!is_dir($this->rootPath)
            && !mkdir($this->rootPath, $this->options->permissionsDir, true)
        ) {
            throw new InvalidArgumentException('Root path does not exist.');
        }
        if (!is_writable($this->rootPath)) {
            throw new InvalidArgumentException('Root path is not writable.');
        }
    }

    #[\Override]
    public function get(string $path): ?SplFileInfo
    {
        $resolvePath = $this->resolvePath($path);

        if (file_exists($resolvePath)) {
            return new SplFileInfo($resolvePath);
        }

        return null;
    }

    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    public function getOptions(): LocalStorageOptions
    {
        return $this->options;
    }

    #[\Override]
    public function writeByPath(
        string               $path,
        string               $contents,
        ?StorageWriteOptions $options = null,
    ): void {
        $options ??= new StorageWriteOptions();

        $fullPath = $this->resolvePath($path);

        $dir = dirname($fullPath);

        if (!$options->createDir
            && !is_dir($dir)
        ) {
            throw StorageException::forWrite(
                path: $path,
                message: 'Directory does not exist and createDir option is disabled.',
            );
        }

        if (!is_dir($dir)
            && !mkdir($dir, $this->options->permissionsDir, true)
            && !is_dir($dir)
        ) {
            throw StorageException::forWrite(
                path: $path,
                message: 'Directory could not be created.',
            );
        }
        if (!is_writable($dir)) {
            throw StorageException::forWrite(
                path: $path,
                message: 'Directory is not writable.',
            );
        }

        if (!file_exists($fullPath)) {
            if (!$options->createFile) {
                throw StorageException::forWrite(
                    path: $path,
                    message: 'File does not exist and createFile option is disabled.',
                );
            }
            touch($fullPath);
            chmod($fullPath, $this->options->permissionsFile);
        }

        if (!is_writable($fullPath)) {
            throw StorageException::forWrite(
                path: $path,
                message: 'File is not writable.',
            );
        }

        $resource = fopen($fullPath, $options->overwrite ? 'w' : 'a');

        if ($resource === false) {
            throw StorageException::forWrite(
                path: $path,
                message: 'File could not be opened for writing.',
            );
        }

        if ($options->atomic) {
            $flock = flock($resource, LOCK_EX);
            if (!$flock) {
                throw StorageException::forWrite(
                    path: $path,
                    message: 'File could not be locked for writing.',
                );
            }
        }

        $bytes = fwrite($resource, $contents);

        if ($bytes === false) {
            throw StorageException::forWrite(
                path: $path,
                message: 'File could not be written.',
            );
        }

        fclose($resource);
    }

    /**
     * @param ?StorageWriteOptions $options
     *                                      return void
     */
    #[\Override]
    public function write(SplFileInfo $fileInfo, string $contents, ?StorageWriteOptions $options = null): void
    {
        if ($fileInfo->isDir()) {
            throw StorageException::forWrite(
                path: $fileInfo->getPathname(),
                message: 'Cannot write directory.',
            );
        }

        if (!$fileInfo->isWritable()) {
            throw StorageException::forWrite(
                path: $fileInfo->getPathname(),
                message: 'File is not writable.',
            );
        }

        $this->writeByPath($fileInfo->getPathname(), $contents, $options);
    }

    #[\Override]
    public function readByPath(string $path): string
    {
        $fullPath = $this->resolvePath($path);

        if (!file_exists($fullPath)) {
            throw StorageException::forRead(
                path: $path,
                message: 'File does not exist.',
            );
        }
        if (!is_readable($fullPath)) {
            throw StorageException::forRead(
                path: $path,
                message: 'File is not readable.',
            );
        }

        $data = file_get_contents($fullPath);
        if ($data === false) {
            throw StorageException::forRead(
                path: $path,
                message: 'File could not be read.',
            );
        }

        return $data;
    }

    #[\Override]
    public function read(SplFileInfo $fileInfo): string
    {
        if ($fileInfo->isDir()) {
            throw StorageException::forRead(
                path: $fileInfo->getPathname(),
                message: 'Cannot read directory.',
            );
        }

        if (!$fileInfo->isReadable()) {
            throw StorageException::forRead(
                path: $fileInfo->getPathname(),
                message: 'File is not readable.',
            );
        }

        return $this->readByPath($fileInfo->getPathname());
    }

    #[\Override]
    public function deleteByPath(string $path): void
    {
        $fullPath = $this->resolvePath($path);

        if (!file_exists($fullPath)) {
            return;
        }

        if (is_dir($fullPath)) {
            $this->deleteDirectoryByPath($fullPath);

            return;
        }

        if (!unlink($fullPath)) {
            throw StorageException::forDelete($path);
        }
    }

    #[\Override]
    public function delete(SplFileInfo $fileInfo): void
    {
        $this->deleteByPath($fileInfo->getPathname());
    }

    /**
     * Recursively delete a directory and all its contents
     *
     *
     * @throws StorageException
     */
    #[\Override]
    public function deleteDirectoryByPath(string $dir): void
    {
        $dir = $this->resolvePath($dir);
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $fileInfo) {
            /** @var SplFileInfo $fileInfo */
            $path = $fileInfo->getPathname();

            if ($fileInfo->isDir()) {
                if (!rmdir($path)) {
                    throw StorageException::forDelete($this->toRelativePath($path));
                }
            } else {
                if (!unlink($path)) {
                    throw StorageException::forDelete($this->toRelativePath($path));
                }
            }
        }

        if (!rmdir($dir)) {
            throw StorageException::forDelete($this->toRelativePath($dir));
        }
    }

    #[\Override]
    public function deleteDirectory(SplFileInfo $dir): void
    {
        if (!$dir->isDir()) {
            throw StorageException::forDelete(
                path: $dir->getPathname(),
                message: 'Cannot delete non-directory.',
            );
        }

        $this->deleteDirectoryByPath($dir->getPathname());
    }

    #[\Override]
    public function fileExists(string $path): bool
    {
        $fullPath = $this->resolvePath($path);

        return file_exists($fullPath) && is_file($fullPath);
    }

    #[\Override]
    public function dirExists(string $path): bool
    {
        $fullPath = $this->resolvePath($path);

        return file_exists($fullPath) && is_dir($fullPath);
    }


    /**
     *
     * @return list<SplFileInfo>
     */
    #[\Override]
    public function listFiles(string $path = '', bool $recursively = false): array
    {
        return $this->list($path, $recursively, StorageListTypeEnum::Files);
    }

    /**
     * @return list<SplFileInfo>
     */
    #[\Override]
    public function listDirs(string $path = '', bool $recursively = false): array
    {
        return $this->list($path, $recursively, StorageListTypeEnum::Directories);
    }

    /**
     * @return list<SplFileInfo>
     */
    #[\Override]
    public function list(
        string              $path = '',
        bool                $recursively = false,
        StorageListTypeEnum $type = StorageListTypeEnum::All,
    ): array {
        $base = $this->resolvePath($path);
        $files = [];
        if (!is_dir($base)) {
            return $files;
        }

        try {
            $iterator = $recursively
                ? new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS),
                )
                : new DirectoryIterator($base);

            foreach ($iterator as $fileInfo) {
                /**
                 * @var SplFileInfo|DirectoryIterator $fileInfo
                 */
                if ($fileInfo instanceof DirectoryIterator && $fileInfo->isDot()) {
                    continue;
                }

                if (
                    ($type === StorageListTypeEnum::Files && !$fileInfo->isFile())
                    || ($type === StorageListTypeEnum::Directories && !$fileInfo->isDir())
                ) {
                    continue;
                }

                $files[] = $fileInfo;
            }
        } catch (Throwable $e) {
            throw StorageException::forList($path, $e->getMessage(), $e);
        }

        return $files;
    }

    private function resolvePath(string $path): string
    {
        $path = ltrim($path, DIRECTORY_SEPARATOR);

        // Prevent path traversal like ../../secrets
        $parts = [];
        foreach (explode(DIRECTORY_SEPARATOR, $path) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                throw new InvalidArgumentException('Invalid storage path.');
            }
            $parts[] = $part;
        }
        $safe = implode(DIRECTORY_SEPARATOR, $parts);

        return $this->rootPath . DIRECTORY_SEPARATOR . $safe;
    }

    private function toRelativePath(string $fullPath): string
    {
        $root = rtrim($this->rootPath, DIRECTORY_SEPARATOR);

        if (str_starts_with($fullPath, $root . DIRECTORY_SEPARATOR)) {
            return substr($fullPath, strlen($root) + 1);
        }

        return $fullPath;
    }
}
