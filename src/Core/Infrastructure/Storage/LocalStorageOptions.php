<?php

namespace Inquisition\Core\Infrastructure\Storage;

use Inquisition\Foundation\Storage\StorageOptionsInterface;

final readonly class LocalStorageOptions implements StorageOptionsInterface
{
    public function __construct(
        public int $permissionsDir = 0775,
        public int $permissionsFile = 0664,
    )
    {
    }
}