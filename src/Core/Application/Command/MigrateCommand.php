<?php

namespace Inquisition\Core\Application\Command;

use Inquisition\Core\Infrastructure\Migration\MigrationDiscovery;
use Inquisition\Core\Infrastructure\Migration\MigrationRunner;

readonly class MigrateCommand implements CommandInterface
{
    private MigrationRunner $migrationRunner;
    private MigrationDiscovery $migrationDiscovery;

    public function __construct()
    {
        $this->migrationRunner = MigrationRunner::getInstance();
        $this->migrationDiscovery = MigrationDiscovery::getInstance();
    }

    /**
     * @return void
     */
    public function up(): void
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
    public function down(int $steps = 1): void
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