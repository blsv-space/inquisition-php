<?php

namespace Inquisition\Foundation;

interface KernelInterface
{
    public function boot(): void;
    public function shutdown(): void;
}