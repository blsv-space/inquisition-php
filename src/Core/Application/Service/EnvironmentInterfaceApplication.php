<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Service;

interface EnvironmentInterfaceApplication extends ApplicationServiceInterface
{
    public protected(set) EnvironmentEnum $mode {
        get;
    }
    public function isDev(): bool;
    public function isProd(): bool;
    public function isTest(): bool;
}
