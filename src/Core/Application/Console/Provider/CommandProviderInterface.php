<?php

namespace Inquisition\Core\Application\Console\Provider;

interface CommandProviderInterface
{
    public function getCommands(): array;
}
