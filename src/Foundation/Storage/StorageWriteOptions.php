<?php

declare(strict_types=1);

namespace Inquisition\Foundation\Storage;

final readonly class StorageWriteOptions
{
    public function __construct(
        public bool $overwrite = true,
        public bool $createDir = true,
        public bool $createFile = true,
        public bool $atomic = true,
    ) {}
}
