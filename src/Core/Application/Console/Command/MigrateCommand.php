<?php

namespace Inquisition\Core\Application\Console\Command;

use Inquisition\Core\Infrastructure\Migration\MigrationDiscovery;
use Inquisition\Core\Infrastructure\Migration\MigrationRunner;

final readonly class MigrateCommand extends AbstractCommand
{
    private MigrationRunner $migrationRunner;
    private MigrationDiscovery $migrationDiscovery;

    public function __construct()
    {
        $this->migrationRunner = MigrationRunner::getInstance();
        $this->migrationDiscovery = MigrationDiscovery::getInstance();
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return 'migrate';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Run database migrations';
    }

    /**
     * @return string
     */
    public function getHelp(): string
    {
        return 'Run database migrations';
    }

    /**
     * @return string[]
     */
    public function getArguments(): array
    {
        return [
            'down' => 'Rollback the last migration',
            'steps' => 'Number of migrations to rollback',
        ];
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $this->up();
    }

    /**
     * @return void
     */
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
     * @return void
     */
    private function down(int $steps = 1): void
    {
        echo "Rolling back migrations...\n";
        $this->discoveryAndRegistration();
        $this->migrationRunner->runDown($steps);
        echo "Rollback completed!\n";
    }


    /**
     * @return void
     */
    private function discoveryAndRegistration(): void
    {
        $migrations = $this->migrationDiscovery->discover();
        foreach ($migrations as $migration) {
            $this->migrationRunner->registerMigration(new $migration());
        }
    }
}