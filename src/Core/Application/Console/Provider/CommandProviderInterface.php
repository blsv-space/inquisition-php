<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Console\Provider;

interface CommandProviderInterface
{
    /**
     * @return class-string[]
     */
    public function getCommands(): array;
}
