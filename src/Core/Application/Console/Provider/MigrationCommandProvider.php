<?php

namespace Inquisition\Core\Application\Console\Provider;

use Inquisition\Core\Application\Console\Command\MigrateCommand;

final readonly class MigrationCommandProvider implements CommandProviderInterface
{
    public function getCommands(): array
    {
        return [
            MigrateCommand::class,
        ];
    }
}