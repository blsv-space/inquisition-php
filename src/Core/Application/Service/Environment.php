<?php

namespace Inquisition\Core\Application\Service;

use Inquisition\Foundation\Config\Config;
use Inquisition\Foundation\Singleton\SingletonTrait;

final class Environment implements EnvironmentInterface
{
    use SingletonTrait;

    protected(set) EnvironmentEnum $mode {
        get {
            return $this->mode;
        }
    }

    private function __construct()
    {
        $config = Config::getInstance();
        $this->mode = EnvironmentEnum::fromString($config->get('app.mode'));
    }

    /**
     * @return bool
     */
    public function isProd(): bool
    {
        return $this->mode->isProduction();
    }

    /**
     * @return bool
     */
    public function isDev(): bool
    {
        return $this->mode->isDevelopment();
    }

    /**
     * @return bool
     */
    public function isTest(): bool
    {
        return $this->mode->isTest();
    }
}