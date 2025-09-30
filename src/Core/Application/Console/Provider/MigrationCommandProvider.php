<?php

namespace Inquisition\Core\Application\Console\Provider;

use Inquisition\Core\Application\Console\Command\MigrateCommand;
use Inquisition\Core\Application\Console\Command\MigrationCreateCommand;

final readonly class MigrationCommandProvider implements CommandProviderInterface
{
    /**
     * @return class-string[]
     */
    public function getCommands(): array
    {
        return [
            MigrateCommand::getAlias() => MigrateCommand::class,
            MigrationCreateCommand::getAlias() => MigrationCreateCommand::class,
        ];
    }
}