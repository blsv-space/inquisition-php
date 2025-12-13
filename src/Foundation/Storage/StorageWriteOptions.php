<?php

namespace Inquisition\Foundation\Storage;

final readonly class StorageWriteOptions
{
    public function __construct(
        public bool $overwrite = true,
        public bool $createDir = true,
        public bool $createFile = true,
        public bool $atomic = true,
        public ?int $permissions = null
    )
    {
    }
}