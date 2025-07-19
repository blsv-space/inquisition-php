<?php

namespace Inquisition\Foundation;

use Inquisition\Foundation\Config\Config;
use Inquisition\Foundation\Config\ConfigInterface;
use Inquisition\Foundation\Singleton\SingletonInterface;
use Inquisition\Foundation\Singleton\SingletonTrait;
class Application implements SingletonInterface
{
    use SingletonTrait;

    private ConfigInterface $config {
        get {
            return $this->config;
        }
    }

    private function __construct() {
        $this->boot();
    }

    public function boot(): void
    {
        $this->config = Config::getInstance();
    }

}
