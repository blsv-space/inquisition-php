<?php

namespace Inquisition\Foundation\Storage\Exception;

use RuntimeException;
use Throwable;

final class StorageException extends RuntimeException
{
    public static function forWrite(string $path, ?Throwable $previous = null): self
    {
        return new self('Failed to write to storage: ' . $path, 0, $previous);
    }

    public static function forRead(string $path, ?Throwable $previous = null): self
    {
        return new self('Failed to read from storage: ' . $path, 0, $previous);
    }

    public static function forDelete(string $path, ?Throwable $previous = null): self
    {
        return new self('Failed to delete from storage: ' . $path, 0, $previous);
    }

    public static function forList(string $prefix, ?Throwable $previous = null): self
    {
        return new self('Failed to list storage prefix: ' . $prefix, 0, $previous);
    }
}