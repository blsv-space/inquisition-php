<?php

namespace Inquisition\Core\Application\Service;

interface EnvironmentInterfaceApplication extends ApplicationServiceInterface
{
    protected(set) EnvironmentEnum $mode {
        get;
    }
    public function isDev(): bool;
    public function isProd(): bool;
    public function isTest(): bool;
}