<?php

declare(strict_types=1);

namespace Inquisition\Foundation;

interface KernelInterface
{
    public string $projectRoot {
        get;
        set;
    }

    public function boot(): void;

    public function shutdown(): void;
}
