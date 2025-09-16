<?php

namespace Inquisition\Foundation;

use Inquisition\Foundation\Config\Config;
use Inquisition\Foundation\Config\ConfigInterface;
use Inquisition\Foundation\Singleton\SingletonInterface;
use Inquisition\Foundation\Singleton\SingletonRegistry;
use Inquisition\Foundation\Singleton\SingletonTrait;
use LogicException;

class Kernel
    implements SingletonInterface, KernelInterface
{
    use SingletonTrait;

    public string $projectRoot {
        get {
            if (!isset($this->projectRoot)) {
                throw new LogicException('Project root is not set');
            }

            return $this->projectRoot;
        }

        set {
            if (isset($this->projectRoot)) {
                throw new LogicException('Project root cannot be changed');
            }

            $this->projectRoot = $value;
        }
    }

    private ConfigInterface $config {
        get {
            return $this->config;
        }
    }

    private function __construct()
    {
        $this->boot();
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        $this->config = Config::getInstance();
    }

    /**
     * @return void
     */
    public function shutdown(): void
    {
        SingletonRegistry::getInstance()->resetAll();
    }

}
