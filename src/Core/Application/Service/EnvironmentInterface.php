<?php

namespace Inquisition\Core\Application\Service;

interface EnvironmentInterface extends ServiceInterface
{
    protected(set) EnvironmentEnum $mode {
        get;
    }
    public function isDev(): bool;
    public function isProd(): bool;
    public function isTest(): bool;
}