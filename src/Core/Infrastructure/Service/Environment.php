<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Service;

use Inquisition\Core\Application\Service\EnvironmentEnum;
use Inquisition\Core\Application\Service\EnvironmentInterfaceApplication;
use Inquisition\Foundation\Config\Config;
use Inquisition\Foundation\Singleton\SingletonTrait;

final class Environment implements EnvironmentInterfaceApplication
{
    use SingletonTrait;

    public protected(set) EnvironmentEnum $mode {
        get {
            return $this->mode;
        }
    }

    private function __construct()
    {
        $config = Config::getInstance();
        $this->mode = EnvironmentEnum::fromString($config->getByPath('app.mode'));
    }

    #[\Override]
    public function isProd(): bool
    {
        return $this->mode->isProduction();
    }

    #[\Override]
    public function isDev(): bool
    {
        return $this->mode->isDevelopment();
    }

    #[\Override]
    public function isTest(): bool
    {
        return $this->mode->isTest();
    }
}
