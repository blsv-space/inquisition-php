<?php

namespace Inquisition\Foundation\Storage\Exception;

use RuntimeException;
use Throwable;

final class StorageException extends RuntimeException
{
    /**
     * @param string $path
     * @param string|null $message
     * @param Throwable|null $previous
     * @return self
     */
    public static function forWrite(string $path, ?string $message = null, ?Throwable $previous = null): self
    {
        return new self('Failed to write to storage: ' . $path
            . ($message ? ' ' . $message : ''), 0, $previous);
    }

    /**
     * @param string $path
     * @param string|null $message
     * @param Throwable|null $previous
     * @return self
     */
    public static function forRead(string $path, ?string $message = null, ?Throwable $previous = null): self
    {
        return new self('Failed to read from storage: ' . $path
            . ($message ? ' ' . $message : ''), 0, $previous);
    }

    /**
     * @param string $path
     * @param string|null $message
     * @param Throwable|null $previous
     * @return self
     */
    public static function forDelete(string $path, ?string $message = null, ?Throwable $previous = null): self
    {
        return new self('Failed to delete from storage: ' . $path
            . ($message ? ' ' . $message : ''), 0, $previous);
    }

    /**
     * @param string $path
     * @param string|null $message
     * @param Throwable|null $previous
     * @return self
     */
    public static function forList(string $path, ?string $message = null, ?Throwable $previous = null): self
    {
        return new self('Failed to list storage prefix: ' . $path
            . ($message ? ' ' . $message : ''), 0, $previous);
    }
}