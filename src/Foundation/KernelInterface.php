<?php

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