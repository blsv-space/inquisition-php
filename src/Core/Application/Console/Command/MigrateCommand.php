<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Console\Command;

use Inquisition\Core\Infrastructure\Migration\MigrationDiscovery;
use Inquisition\Core\Infrastructure\Migration\MigrationRunner;

final class MigrateCommand extends AbstractCommand
{
    private const string ARGUMENT_DOWN = 'down';
    private const string ARGUMENT_STEPS = 'steps';

    private MigrationRunner $migrationRunner;
    private MigrationDiscovery $migrationDiscovery;

    public function __construct($parameters = [])
    {
        $this->parameters = $parameters;
        $this->migrationRunner = MigrationRunner::getInstance();
        $this->migrationDiscovery = MigrationDiscovery::getInstance();
    }

    #[\Override]
    public static function getAlias(): string
    {
        return 'migration:migrate';
    }

    /**
     * @return string[]
     */
    #[\Override]
    public static function getArguments(): array
    {
        return [
            self::ARGUMENT_DOWN => 'Rollback the last migration',
            self::ARGUMENT_STEPS => 'Number of migrations to rollback',
        ];
    }

    #[\Override]
    public function getDescription(): string
    {
        return 'Run database migrations';
    }

    #[\Override]
    public function getHelp(): string
    {
        return 'Run database migrations';
    }

    #[\Override]
    public function execute(): void
    {
        if (isset($this->parameters[self::ARGUMENT_DOWN])) {
            $steps = 1;
            if (array_key_exists(self::ARGUMENT_STEPS, $this->parameters)
                && is_numeric($this->parameters[self::ARGUMENT_STEPS])
                && $this->parameters[self::ARGUMENT_STEPS] > 1
            ) {
                $steps = (int) $this->parameters[self::ARGUMENT_STEPS];
            }
            $this->down($steps);

            return;
        }

        $this->up();
    }

    private function up(): void
    {
        echo "Running migrations...\n";
        $this->discoveryAndRegistration();
        $this->migrationRunner->runUp();
        echo "Migration completed!\n";
    }

    /**
     * @param int $steps Number of migrations that will be rolled back
     *
     */
    private function down(int $steps): void
    {
        echo "Rolling back migrations...\n";
        $this->discoveryAndRegistration();
        $this->migrationRunner->runDown($steps);
        echo "Rollback completed!\n";
    }


    private function discoveryAndRegistration(): void
    {
        $migrations = $this->migrationDiscovery->discover();
        foreach ($migrations as $migration) {
            $this->migrationRunner->registerMigration(new $migration());
        }
    }
}
